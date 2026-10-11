<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClientTemplateMail;
use App\Models\ClientEmail;
use App\Models\Project;
use App\Support\ClientEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Имейли до клиенти по шаблон: избор на шаблон, попълване, преглед и изпращане. */
class ClientEmailController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.client-emails.index', [
            'templates' => ClientEmails::all(),
            'project' => $this->project($request),
            'emails' => ClientEmail::with(['project', 'user'])->latest()->paginate(20),
        ]);
    }

    public function create(Request $request, string $template)
    {
        $tpl = $this->template($template);
        $project = $this->project($request);

        $defaults = collect($tpl['fields'])->map(fn ($field) => $field['default'] ?? '')->all();
        $defaults['sender'] = $request->user()->name;
        if (array_key_exists('valid_until', $defaults)) {
            $defaults['valid_until'] = now()->addDays(7)->toDateString();
        }
        if ($project) {
            $defaults['client_name'] = str_replace('Запитване от ', '', $project->name);
        }

        return view('admin.client-emails.create', [
            'tpl' => $tpl,
            'project' => $project,
            'defaults' => $defaults,
            'to' => $project?->client_email,
            'mailNotConfigured' => in_array(config('mail.default'), ['log', 'array'], true),
        ]);
    }

    /** Показва имейла така, както ще го види клиентът. Празните задължителни полета се виждат като [Поле]. */
    public function preview(Request $request, string $template)
    {
        $tpl = $this->template($template);
        $data = [];
        foreach ($tpl['fields'] as $name => $field) {
            $value = trim((string) data_get($request->input('data'), $name, ''));
            if ($value === '' && ($field['required'] ?? true)) {
                $value = ($field['type'] ?? 'text') === 'date' ? now()->addDays(7)->toDateString() : '[' . $field['label'] . ']';
            }
            $data[$name] = $value;
        }
        if (($tpl['fields']['valid_until'] ?? null) && strtotime($data['valid_until']) === false) {
            $data['valid_until'] = now()->addDays(7)->toDateString();
        }

        return view($tpl['view'], [
            'data' => $data,
            'package' => $tpl['package'] ?? null,
            'subject' => $request->input('subject') ?: $tpl['subject'],
        ]);
    }

    public function store(Request $request, string $template)
    {
        $tpl = $this->template($template);
        $validated = $request->validate([
            'to' => 'required|email:rfc|max:255',
            'subject' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ] + ClientEmails::rules($tpl), [
            'to.required' => 'Въведете имейла на клиента.',
            'to.email' => 'Имейлът на клиента не е валиден.',
            'data.valid_until.after_or_equal' => 'Датата на валидност не може да е в миналото.',
        ], collect($tpl['fields'])->mapWithKeys(fn ($f, $name) => ["data.$name" => '„' . $f['label'] . '“'])->all());

        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $validated['data']);

        $log = ClientEmail::create([
            'project_id' => $validated['project_id'] ?? null,
            'user_id' => $request->user()->id,
            'template' => $tpl['key'],
            'to_email' => $validated['to'],
            'subject' => $validated['subject'],
            'data' => $data,
        ]);

        try {
            Mail::to($validated['to'])->send(new ClientTemplateMail($tpl, $data, $validated['subject']));
        } catch (Throwable $e) {
            Log::error('Client email failed', ['client_email_id' => $log->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return back()->withInput()->with('send_error', 'Имейлът не беше изпратен: ' . $e->getMessage());
        }

        $log->forceFill(['sent_at' => now()])->save();

        // Ако запитването още няма имейл, запазваме този, на който писахме.
        if ($log->project && ! $log->project->client_email) {
            $log->project->forceFill(['client_email' => $validated['to']])->save();
        }

        return redirect()->route('admin.client-emails.index')
            ->with('success', '„' . $tpl['label'] . '“ е изпратен до ' . $validated['to'] . '.');
    }

    private function template(string $key): array
    {
        $tpl = ClientEmails::find($key);
        abort_unless($tpl, 404);

        return $tpl;
    }

    private function project(Request $request): ?Project
    {
        $id = $request->query('project', $request->input('project_id'));

        return $id ? Project::find($id) : null;
    }
}
