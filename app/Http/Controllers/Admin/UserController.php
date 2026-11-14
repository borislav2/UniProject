<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Profiles;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')
            ->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        return view('admin.users.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile' => ['nullable', Rule::in(Profiles::keys())],
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile' => Profiles::normalize($request->input('profile')),
        ]);
        $this->syncAccess($user, $request->boolean('manages_users'));
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Потребителят е създаден.');
    }
    
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }
    
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile' => ['nullable', Rule::in(Profiles::keys())],
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('profile')) {
            $data['profile'] = $request->input('profile');
        }
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        // Никой не може да си отнеме сам управлението на потребителите (иначе може да няма кой да го върне).
        $this->syncAccess($user, $request->boolean('manages_users') || $user->is($request->user()));
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Промените са запазени.');
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Потребителят е изтрит.');
    }

    /**
     * Всеки акаунт, създаден тук, влиза в админа. Без роля входът връща към началната страница,
     * затова ролята се задава винаги: admin (управлява и потребителите) или project-manager.
     */
    private function syncAccess(User $user, bool $managesUsers): void
    {
        // Старите роли project-manager и developer дават един и същ достъп: запазват се, ако вече ги има.
        if (! $managesUsers && $user->hasAnyRole(['project-manager', 'developer']) && ! $user->isAdmin()) {
            return;
        }

        $role = Role::where('slug', $managesUsers ? 'admin' : 'project-manager')->first();

        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }
}
