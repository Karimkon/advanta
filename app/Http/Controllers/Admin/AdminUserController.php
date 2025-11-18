<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::latest()->paginate(10) // Change this line
        ]);
    }

    public function create()
    {
        $roles = [
            'admin',
            'operations',
            'procurement',
            'finance',
            'stores',
            'ceo',
            'project_manager',
            'engineer',
            'supplier',
            'surveyor'
        ];

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:50',
            'password' => 'required|min:6',
            'role'     => 'required|string'
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        $roles = [
            'admin',
            'operations',
            'procurement',
            'finance',
            'stores',
            'ceo',
            'project_manager',
            'engineer',
            'supplier',
            'surveyor'
        ];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:50',
        'role' => 'required|string',
        'password' => 'nullable|min:6|confirmed',
    ]);

    $updateData = [
        'name' => $request->name,
        'phone' => $request->phone,
        'role' => $request->role,
    ];

    // Only update password if provided
    if ($request->filled('password')) {
        $updateData['password'] = Hash::make($request->password);
    }

    $user->update($updateData);

    return redirect()->route('admin.users.index')
        ->with('success', 'User updated successfully');
}

    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }
}