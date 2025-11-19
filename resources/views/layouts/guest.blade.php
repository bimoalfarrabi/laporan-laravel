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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="w-full px-4 sm:px-0">
                <div class="flex justify-center items-center gap-4">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                    <a href="https://ptwba.com" target="_blank">
                        <img src="https://ptwba.com/assets/image/logowba.png" alt="WBA Logo" class="w-20 h-20 object-contain">
                    </a>
                </div>
                <div class="text-center mt-2 text-gray-800 text-xl font-semibold">
                    e-Satpam RSUD Blambangan
                </div>
            </div>

            <div class="w-full sm:max-w-xs md:max-w-md mt-6 px-3 sm:px-4 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
