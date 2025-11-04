<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Pengguna') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <p><strong>Nama:</strong> {{ $user->name }}</p>
                        <p><strong>Username:</strong> {{ $user->username }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>NIK:</strong> {{ $user->nik }}</p>
                        <p><strong>No. HP:</strong> {{ $user->phone_number }}</p>
                        <p><strong>Peran:</strong> {{ $user->roles->pluck('name')->join(', ') }}</p>
                        <p><strong>Waktu Dibuat:</strong> <x-waktu-dibuat :date="$user->created_at" /></p>
                        <p><strong>Terakhir Login:</strong>
                            @if ($user->last_login_at)
                                <x-waktu-dibuat :date="$user->last_login_at" />
                            @else
                                Belum pernah login
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>