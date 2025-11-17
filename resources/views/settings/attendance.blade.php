<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Waktu Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">Ada beberapa masalah dengan input Anda.</span>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('settings.attendance.update') }}" method="POST">
                        @csrf
                        <div class="space-y-8">
                            @foreach ($shifts as $shift)
                                <div class="p-4 border rounded-lg">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 capitalize">
                                        Shift {{ str_replace('_', ' ', $shift) }}
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach ($types as $type)
                                            <div class="p-4 border rounded-md">
                                                <h4 class="text-md font-medium text-gray-800 mb-2 capitalize">
                                                    Absensi {{ $type }}
                                                </h4>
                                                <div class="space-y-2">
                                                    @foreach ($times as $time)
                                                        @php
                                                            $key = "attendance_{$shift}_{$type}_{$time}";
                                                            $label = ucfirst($type) . ' ' . ucfirst($time);
                                                        @endphp
                                                        <div>
                                                            <label for="{{ $key }}"
                                                                class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                                                            <input type="time" name="{{ $key }}" id="{{ $key }}"
                                                                value="{{ old($key, $settings[$key] ?? '') }}"
                                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end">
                            <x-primary-button type="submit">
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
