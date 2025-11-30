<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->query('search');
        $filterRole = $request->query('role');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        if (Auth::user()->hasRole('superadmin')) {
            $query = User::with('roles');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            if ($filterRole) {
                $query->whereHas('roles', function ($q) use ($filterRole) {
                    $q->where('name', $filterRole);
                });
            }
            $users = $query->orderBy($sortBy, $sortDirection)->paginate(15);
        } elseif (Auth::user()->hasRole('danru')) {
            // danru hanya melihat pengguna dengan peran anggota
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['anggota', 'backup']);
            })
                ->with('roles')
                ->orderBy($sortBy, $sortDirection)
                ->paginate(15);
        } elseif (Auth::user()->hasRole('manajemen')) {
            $query = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['anggota', 'danru']);
            })->with('roles');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            if ($filterRole) {
                $query->whereHas('roles', function ($q) use ($filterRole) {
                    $q->where('name', $filterRole);
                });
            }
            $users = $query->orderBy($sortBy, $sortDirection)->paginate(15);
        } else {
            $users = collect(); // anggota tidak melihat daftar pengguna
        }

        $roles = Role::all();

        if ($request->ajax()) {
            return view('users._results', compact(
                'users',
                'roles',
                'search',
                'filterRole',
                'sortBy',
                'sortDirection'
            ))->render();
        }

        return view('users.index', compact('users', 'roles', 'search', 'filterRole', 'sortBy', 'sortDirection'));
    }

    public function create()
    {
        $user = Auth::user();
        $roles = collect(); // Default to an empty collection

        if ($user->hasRole('superadmin')) {
            $roles = Role::all(); // Superadmin can assign any role
        } elseif ($user->hasRole('danru')) {
            $roles = Role::whereIn('name', ['anggota', 'backup'])->get(); // Danru can assign 'anggota' or 'backup'
        }

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {

        $this->authorize('create', User::class);

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'nik' => 'required|string|digits:16',
            'phone_number' => ['nullable', 'string', 'regex:/^(08|\\+628)[0-9]{8,11}$/'],
        ];

        if (Auth::user()->hasRole('superadmin')) {
            $rules['shift'] = 'in:pagi,sore,malam';
        }

        $request->validate($rules);

        if (!Role::where('name', $request->role)->exists()) {
            return redirect()->back()->withErrors(['role' => 'Peran yang dipilih tidak valid'])->withInput();
        }

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
        ];

        if (Auth::user()->hasRole('superadmin')) {
            $userData['shift'] = $request->shift;
        } elseif (Auth::user()->hasRole('danru')) {
            $userData['shift'] = Auth::user()->shift;
            // Ensure danru can only create 'anggota' or 'backup'
            if (!in_array($request->role, ['anggota', 'backup'])) {
                return redirect()->back()->withErrors(['role' => 'Anda hanya dapat membuat pengguna dengan peran anggota atau backup.'])->withInput();
            }
        }

        return DB::transaction(function () use ($request, $userData) {
            $user = User::create($userData);

            $user->assignRole($request->role); // tugaskan peran menggunakan spatie

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
        });
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $loggedInUser = Auth::user();
        $roles = collect(); // Default to an empty collection

        if ($loggedInUser->hasRole('superadmin')) {
            $roles = Role::all(); // Superadmin can assign any role
        } elseif ($loggedInUser->hasRole('danru')) {
            $roles = Role::whereIn('name', ['anggota', 'backup'])->get(); // Danru can assign 'anggota' or 'backup'
        }

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string',
            'nik' => 'nullable|string|digits:16',
            'phone_number' => ['nullable', 'string', 'regex:/^(08|\\+628)[0-9]{8,11}$/'],
        ];

        if (Auth::user()->hasRole('superadmin')) {
            $rules['shift'] = 'in:pagi,sore,malam';
        }

        $request->validate($rules);

        if (!Role::where('name', $request->role)->exists()) {
            return redirect()->back()->withErrors(['role' => 'Peran yang dipilih tidak valid'])->withInput();
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->nik = $request->nik;
        $user->phone_number = $request->phone_number;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role; // perbarui kolom role juga

        if (Auth::user()->hasRole('superadmin')) {
            $user->shift = $request->shift;
        } elseif (Auth::user()->hasRole('danru')) {
            // Ensure danru can only assign 'anggota' or 'backup'
            if (!in_array($request->role, ['anggota', 'backup'])) {
                return redirect()->back()->withErrors(['role' => 'Anda hanya dapat mengubah pengguna dengan peran anggota atau backup.'])->withInput();
            }
            // Danru cannot change shift
            if ($user->shift !== Auth::user()->shift) {
                abort(403, 'Anda tidak dapat mengubah shift pengguna di luar shift Anda.');
            }
        }

        return DB::transaction(function () use ($user, $request) {
            $user->save();

            // Perbarui peran Spatie
            $user->syncRoles([$request->role]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
        });
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Cegah penghapusan diri sendiri
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus diri sendiri.');
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        $this->authorize('resetPassword', $user);

        DB::transaction(function () use ($user) {
            $user->password = Hash::make('123456');
            $user->must_reset_password = true;
            $user->save();
        });

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
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            if ($filterRole) {
                $query->role($filterRole);
            }
        } elseif (Auth::user()->hasRole('danru')) {
            // Danru hanya bisa melihat anggota di arsip
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'anggota');
            });

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
        } else {
            // Selain superadmin dan danru, tidak bisa melihat arsip pengguna
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

        DB::transaction(function () use ($user) {
            $user->restore();
        });

        return redirect()->route('users.index')->with('success', 'Pengguna ' . $user->name . ' berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);

        DB::transaction(function () use ($user) {
            $user->forceDelete();
        });

        return redirect()->route('users.archive')->with('success', 'Pengguna ' . $user->name . ' berhasil dihapus permanen');
    }
}
