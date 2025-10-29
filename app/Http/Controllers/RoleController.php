<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Otorisasi langsung untuk memastikan hanya superadmin yang bisa mengakses
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Otorisasi langsung untuk memastikan hanya superadmin yang bisa menyimpan
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        // Convert name to lowercase before creation
        $validated['name'] = strtolower($validated['name']);

        Role::create($validated);

        return redirect()->route('role-permissions.index')->with('success', 'Role baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'superadmin') {
            abort(403, 'The superadmin role cannot be edited.');
        }
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }
        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            abort(403, 'The superadmin role cannot be edited.');
        }
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
        ]);

        $role->update(['name' => $request->name]);

        return redirect()->route('role-permissions.index')->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            abort(403, 'The superadmin role cannot be deleted.');
        }
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $role->delete(); // Soft delete

        return redirect()->route('role-permissions.index')->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Display a listing of the archived resources.
     */
    public function archive()
    {
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $roles = Role::onlyTrashed()->get();
        return view('roles.archive', compact('roles'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($roleId)
    {
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $role = Role::withTrashed()->findOrFail($roleId);
        $role->restore();

        return redirect()->route('roles.archive')->with('success', 'Role berhasil dipulihkan.');
    }
}
