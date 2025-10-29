<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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
        // Pastikan hanya superadmin yang bisa menyimpan
        $this->authorize('create', Role::class);

        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        // Buat role baru
        Role::create(['name' => $request->name]);

        return redirect()->route('role-permissions.index')->with('success', 'Role baru berhasil dibuat.');
    }
}
