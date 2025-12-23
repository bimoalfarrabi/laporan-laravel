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

            // Polling for new notifications
            @auth
            let lastNotificationCount = {{ auth()->user()->unreadNotifications->count() }};

            setInterval(() => {
                fetch('{{ route('notifications.check') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.unread_count > lastNotificationCount) {
                            // Play sound or show toast
                            const Toast = Swal.mixin({
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                            Toast.fire({
                                icon: "info",
                                title: "Notifikasi Baru!",
                                text: "Klik untuk melihat detail."
                            }).then(() => {
                                window.location.reload();
                            });

                            // Update Badge
                            const badge = document.querySelector('.notification-badge');
                            if (badge) {
                                badge.innerText = data.unread_count;
                            } else {
                                // Create badge if not exists (simplified, better to reload or use more complex DOM manipulation)
                                // For now, reloading is a safe bet for complex UI updates, but let's try to update the count if element exists
                            }
                        }
                        lastNotificationCount = data.unread_count;
                    });
            }, 5000); // Check every 5 seconds

            // Web Push Subscription
            if ('serviceWorker' in navigator && 'PushManager' in window) {
                navigator.serviceWorker.register('/sw.js').then(function(swReg) {
                    console.log('Service Worker is registered', swReg);

                    swReg.pushManager.getSubscription().then(function(subscription) {
                        if (subscription === null) {
                            // User is not subscribed, ask for permission
                            Notification.requestPermission().then(function(permission) {
                                if (permission === 'granted') {
                                    subscribeUser(swReg);
                                }
                            });
                        } else {
                            // User is already subscribed
                            console.log('User is already subscribed');
                            updateSubscriptionOnServer(subscription);
                        }
                    });
                }).catch(function(error) {
                    console.error('Service Worker Error', error);
                });
            }

            function subscribeUser(swReg) {
                const applicationServerKey = urlB64ToUint8Array('{{ config('webpush.vapid.public_key') }}');
                swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                }).then(function(subscription) {
                    console.log('User is subscribed:', subscription);
                    updateSubscriptionOnServer(subscription);
                }).catch(function(err) {
                    console.log('Failed to subscribe the user: ', err);
                });
            }

            function updateSubscriptionOnServer(subscription) {
                fetch('{{ route('notifications.subscribe') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(subscription)
                });
            }

            function urlB64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');

                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);

                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }
        @endauth
        });
    </script>
</body>

</html>
