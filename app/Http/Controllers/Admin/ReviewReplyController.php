<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewReply;
use App\Support\Reviews\ReviewReplyWriter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** „Отговори на отзиви“: чернова на отговор на отзив в Google за клиент, публикува се ръчно. */
class ReviewReplyController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? ReviewReply::find($request->integer('from')) : null;

        return view('admin.reviews.index', [
            'replies' => ReviewReply::latest()->paginate(20),
            'businesses' => ReviewReply::query()->distinct()->orderBy('business_name')->pluck('business_name'),
            'defaults' => [
                'business_name' => $from?->business_name,
                'business_type' => $from?->business_type,
                'tone' => $from?->tone ?? 'friendly',
                'signature' => $from?->signature,
            ],
            'usesClaude' => ReviewReplyWriter::usesClaude(),
        ]);
    }

    public function store(Request $request, ReviewReplyWriter $writer)
    {
        $input = $request->validate([
            'business_name' => 'required|string|max:150',
            'business_type' => 'nullable|string|max:100',
            'reviewer_name' => 'nullable|string|max:100',
            'rating' => 'required|integer|between:1,5',
            'review_text' => 'required|string|max:5000',
            'tone' => ['required', Rule::in(array_keys(ReviewReply::TONES))],
            'notes' => 'nullable|string|max:1000',
            'signature' => 'nullable|string|max:150',
        ], [
            'review_text.required' => 'Поставете текста на отзива.',
            'rating.required' => 'Изберете оценката (звездите) на отзива.',
        ]);
        $input['rating'] = (int) $input['rating'];

        $draft = $writer->write($input);
        $reply = ReviewReply::create($input + [
            'user_id' => $request->user()->id,
            'reply_text' => $draft['text'],
            'source' => $draft['source'],
        ]);

        return redirect()->route('admin.reviews.show', $reply)
            ->with($draft['error'] ? 'send_error' : 'success', $draft['error'] ?? 'Черновата е готова. Прегледайте я, преди да я публикувате.');
    }

    public function show(ReviewReply $reply)
    {
        return view('admin.reviews.show', ['reply' => $reply]);
    }

    public function update(Request $request, ReviewReply $reply)
    {
        $reply->update($request->validate(['reply_text' => 'required|string|max:5000']));

        return back()->with('success', 'Отговорът е запазен.');
    }
}
