<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <strong>Judul:</strong> {{ $announcement->title }}
                    </div>
                    <div class="mb-4">
                        <strong>Isi Pengumuman:</strong>
                        <p class="whitespace-pre-wrap">@markdown($announcement->content)</p>
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Oleh:</strong> {{ $announcement->user->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Pada:</strong> <x-waktu-dibuat :date="$announcement->created_at" />
                    </div>
                    <div class="mb-4">
                        <strong>Terakhir Diperbarui:</strong> <x-waktu-dibuat :date="$announcement->updated_at" />
                    </div>
                    <div class="mb-4">
                        <strong>Tanggal Kedaluwarsa:</strong>
                        @if ($announcement->expires_at)
                            <x-waktu-dibuat :date="$announcement->expires_at" />
                            @if ($announcement->expires_at->isPast())
                                <span class="text-red-500 font-semibold">(Kedaluwarsa)</span>
                            @endif
                        @else
                            Tidak Ada
                        @endif
                    </div>

                    <div class="flex items-center justify-start mt-6">
                        <a href="{{ route('announcements.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Kembali ke Daftar') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
