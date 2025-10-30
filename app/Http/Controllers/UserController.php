<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->query('search');
        $filterRole = $request->query('role');

        if (Auth::user()->hasRole('superadmin')) {
            $users = User::with('roles')->latest()->get();
        } elseif (Auth::user()->hasRole('danru')) {
            // danru hanya melihat pengguna dengan peran anggota
            $anggotaRole = Role::where('name', 'anggota')->first();
            $users = $anggotaRole ? $anggotaRole->users()->with('roles')->latest()->get() : collect();
        } else {
            $users = collect(); // anggota tidak melihat daftar pengguna
        }

        $roles = Role::all();
        return view('users.index', compact('users', 'roles', 'search', 'filterRole'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $roles = Role::all(); // ambil seluruh role dari spatie
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {

        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string', // pastikan role valid
            'nik' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
        ]);

        if (!Role::where('name', $request->role)->exists()) {
            return redirect()->back()->withErrors(['role' => 'Peran yang dipilih tidak valid'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // simpan role di kolom role juga
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
        ]);

        $user->assignRole($request->role); // tugaskan peran menggunakan spatie

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $roles = Role::all(); // ambil seluruh role dari spatie
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string',
            'nik' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
        ]);

        if (!Role::where('name', $request->role)->exists()) {
            return redirect()->back()->withErrors(['role' => 'Peran yang dipilih tidak valid'])->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->nik = $request->nik;
        $user->phone_number = $request->phone_number;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role; // perbarui kolom role juga
        $user->save();

        // Perbarui peran Spatie
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Cegah penghapusan diri sendiri
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus diri sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        $this->authorize('resetPassword', $user);

        $user->password = Hash::make('123456');
        $user->must_reset_password = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Password pengguna ' . $user->name . ' berhasil di-reset ke "123456". Pengguna akan diminta untuk mengubah password saat login berikutnya.');
    }

    public function archive(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->query('search');
        $filterRole = $request->query('role');

        $query = User::onlyTrashed()->with('roles'); // Hanya mengambil yang di-soft delete

        if (Auth::user()->hasRole('superadmin')) {
            // SuperAdmin melihat semua pengguna di arsip
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            if ($filterRole) {
                $query->role($filterRole);
            }
        } else {
            // Danru tidak bisa melihat arsip pengguna
            abort(403, 'Anda tidak memiliki akses ke arsip pengguna.');
        }

        $users = $query->latest()->get();
        $roles = Role::all();

        return view('users.archive', compact('users', 'roles', 'search', 'filterRole'));
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('restore', $user);

        $user->restore();

        return redirect()->route('users.index')->with('success', 'Pengguna ' . $user->name . ' berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);

        $user->forceDelete();

        return redirect()->route('users.archive')->with('success', 'Pengguna ' . $user->name . ' berhasil dihapus permanen');
    }
}
