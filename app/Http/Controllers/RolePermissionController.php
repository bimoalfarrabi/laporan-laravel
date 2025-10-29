<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        // Hanya superadmin yang bisa mengakses controller ini
        $this->middleware('role:superadmin');
    }

    /**
     * Menampilkan daftar peran yang bisa diatur.
     */
    public function index()
    {
        // Ambil peran selain superadmin
        $roles = Role::where('name', '!=', 'superadmin')->get();
        return view('role-permissions.index', compact('roles'));
    }

    /**
     * Menampilkan form untuk mengedit hak akses sebuah peran.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Mengelompokkan permission berdasarkan nama model (e.g., 'reports')
            return explode(':', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->all();

        return view('role-permissions.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update hak akses untuk sebuah peran.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('role-permissions.index')->with('success', 'Hak akses untuk peran ' . $role->name . ' berhasil diperbarui.');
    }
}
