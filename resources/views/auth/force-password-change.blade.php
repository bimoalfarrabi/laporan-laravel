<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Password Anda telah direset oleh administrator. Mohon atur password baru Anda.') }}
    </div>

    {{-- Tambahkan ini untuk menampilkan error validasi --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-auth-validation-errors class="mb-4" :errors="$errors" />
    {{-- End Tambahan --}}

    <form method="POST" action="{{ route('password.update-forced') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />
            @error('password')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            @error('password_confirmation')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Atur Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
