<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('store')->latest()->paginate(10)
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

        // Get stores that don't have a manager assigned
        $availableStores = Store::whereDoesntHave('manager')->get();

        return view('admin.users.create', compact('roles', 'availableStores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:50',
            'password' => 'required|min:6',
            'role'     => 'required|string',
            'shop_id'  => 'nullable|exists:stores,id'
        ]);

        $userData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ];

        // Only assign store if role is 'stores'
        if ($request->role === 'stores' && $request->shop_id) {
            $userData['shop_id'] = $request->shop_id;
        }

        User::create($userData);

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

        // Get stores that don't have a manager OR are assigned to this user
        $availableStores = Store::where(function($query) use ($user) {
            $query->whereDoesntHave('manager')
                  ->orWhere('id', $user->shop_id);
        })->get();

        return view('admin.users.edit', compact('user', 'roles', 'availableStores'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string',
            'password' => 'nullable|min:6|confirmed',
            'shop_id' => 'nullable|exists:stores,id'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
        ];

        // Handle store assignment
        if ($request->role === 'stores') {
            $updateData['shop_id'] = $request->shop_id;
        } else {
            // Clear store assignment if role is not stores
            $updateData['shop_id'] = null;
        }

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