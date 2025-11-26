<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        // Check for saved theme in database (injected via Blade) or localStorage, or system preference
        const userTheme = "{{ Auth::user()->theme ?? '' }}";
        const localTheme = localStorage.theme;
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

        let theme;
        if (userTheme) {
            theme = userTheme;
        } else if (localTheme) {
            theme = localTheme;
        } else {
            theme = systemTheme;
        }

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Ensure localStorage is in sync if userTheme is present
        if (userTheme) {
            localStorage.theme = userTheme;
        }
    </script>

    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    <!-- Scripts -->
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
        <footer class="bg-white dark:bg-gray-800 shadow py-2 mt-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                &copy; {{ date('Y') }} IT RSBL. All rights reserved.
            </div>
        </footer>
    </div>

    {{-- Video Compressor Utility --}}
    <script src="{{ asset('js/video-compressor.js') }}"></script>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // SweetAlert for success messages
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            // SweetAlert for error messages
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: {!! json_encode(session('error')) !!},
                    showConfirmButton: true,
                });
            @endif

            // SweetAlert for validation errors
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan Validasi!',
                    html: '<ul>' +
                        @foreach ($errors->all() as $error)
                            '<li>{{ $error }}</li>' +
                        @endforeach
                    '</ul>',
                    showConfirmButton: true,
                });
            @endif

            // SweetAlert for confirmation dialogs (e.g., delete, reset password)
            document.querySelectorAll('[data-confirm-dialog="true"]').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    const swalTitle = this.dataset.swalTitle || 'Apakah Anda yakin?';
                    const swalText = this.dataset.swalText ||
                        'Tindakan ini tidak dapat dibatalkan.';

                    Swal.fire({
                        title: swalTitle,
                        text: swalText,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, lakukan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>
