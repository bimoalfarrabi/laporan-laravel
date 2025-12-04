<nav x-data="{
    open: false,
    darkMode: document.documentElement.classList.contains('dark'),
    toggleTheme() {
        this.darkMode = !this.darkMode;
        const theme = this.darkMode ? 'dark' : 'light';

        // Update DOM
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Update localStorage
        localStorage.theme = theme;

        // Sync with server
        fetch('{{ route('profile.updateTheme') }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({ theme: theme })
        });
    }
}" x-init="$watch('darkMode', val => {
    // Watcher kept for reactivity if needed, but main logic moved to toggleTheme
})"
    class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 transition-colors duration-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Absensi') }}</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                @hasanyrole('danru|anggota|backup')
                                    <x-dropdown-link :href="route('attendances.create')">
                                        {{ __('Buat Absensi') }}
                                    </x-dropdown-link>
                                @endhasanyrole

                                @hasanyrole('superadmin|manajemen|danru')
                                    <x-dropdown-link :href="route('attendances.index')">
                                        {{ __('Daftar Absensi') }}
                                    </x-dropdown-link>
                                @endhasanyrole

                                @can('create', App\Models\LeaveRequest::class)
                                    <x-dropdown-link :href="route('leave-requests.create')">
                                        {{ __('Pengajuan Izin') }}
                                    </x-dropdown-link>
                                @endcan

                                <x-dropdown-link :href="route('leave-requests.index')">
                                    {{ __('Daftar Pengajuan Izin') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @can('viewAny', App\Models\Report::class)
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                            {{ __('Laporan') }}
                        </x-nav-link>
                    @endcan

                    @hasanyrole('superadmin|danru')
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>{{ __('Manajemen') }}</div>

                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    @role('superadmin')
                                        <x-dropdown-link :href="route('report-types.index')">
                                            {{ __('Manajemen Jenis Laporan') }}
                                        </x-dropdown-link>
                                    @endrole
                                    <x-dropdown-link :href="route('users.index')">
                                        {{ __('Manajemen Pengguna') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('announcements.index')">
                                        {{ __('Pengumuman') }}
                                    </x-dropdown-link>
                                    @role('superadmin')
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        <x-dropdown-link :href="route('role-permissions.index')">
                                            {{ __('Manajemen Hak Akses') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('settings.location')">
                                            {{ __('Pengaturan Lokasi Absensi') }}
                                        </x-dropdown-link>
                                    @endrole
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endhasanyrole
                </div>
            </div>



            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notification Dropdown -->
                <div class="mr-2 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-400"></span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300">
                                Notifikasi
                            </div>
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <x-dropdown-link :href="route('notifications.markAsRead', $notification->id)" class="text-sm">
                                    <div class="flex flex-col">
                                    @if ($notification->type === 'App\Notifications\NewReportNotification')
                                        <span class="font-medium">{{ $notification->data['user_name'] }} membuat laporan baru</span>
                                    @elseif ($notification->type === 'App\Notifications\ReportStatusNotification')
                                        <span class="font-medium">Laporan {{ $notification->data['report_type'] }} Anda {{ $notification->data['status'] }}</span>
                                    @endif
                                        <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </x-dropdown-link>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada notifikasi baru
                                </div>
                            @endforelse
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                <x-dropdown-link :href="route('notifications.markAllAsRead')" class="text-center text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                    Tandai semua sudah dibaca
                                </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="toggleTheme()"
                    class="p-2 mr-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700">
                    <!-- Sun Icon -->
                    <svg x-show="!darkMode" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform rotate-90 scale-50"
                        x-transition:enter-end="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave-end="opacity-0 transform -rotate-90 scale-50" class="w-5 h-5"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z"
                            fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <!-- Moon Icon -->
                    <svg x-show="darkMode" style="display: none;" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -rotate-90 scale-50"
                        x-transition:enter-end="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave-end="opacity-0 transform rotate-90 scale-50" class="w-5 h-5"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <!-- Mobile Notification Dropdown -->
                <div class="mr-2 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="relative p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-400"></span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300">
                                Notifikasi
                            </div>
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <x-dropdown-link :href="route('notifications.markAsRead', $notification->id)" class="text-sm">
                                    <div class="flex flex-col">
                                    @if ($notification->type === 'App\Notifications\NewReportNotification')
                                        <span class="font-medium">{{ $notification->data['user_name'] }} membuat laporan baru</span>
                                    @elseif ($notification->type === 'App\Notifications\ReportStatusNotification')
                                        <span class="font-medium">Laporan {{ $notification->data['report_type'] }} Anda {{ $notification->data['status'] }}</span>
                                    @endif
                                        <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </x-dropdown-link>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada notifikasi baru
                                </div>
                            @endforelse
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                <x-dropdown-link :href="route('notifications.markAllAsRead')" class="text-center text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                    Tandai semua sudah dibaca
                                </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Dark Mode Toggle Mobile -->
                <button @click="toggleTheme()"
                    class="p-2 mr-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700">
                    <!-- Sun Icon -->
                    <svg x-show="!darkMode" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform rotate-90 scale-50"
                        x-transition:enter-end="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave-end="opacity-0 transform -rotate-90 scale-50" class="w-5 h-5"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z"
                            fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <!-- Moon Icon -->
                    <svg x-show="darkMode" style="display: none;" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -rotate-90 scale-50"
                        x-transition:enter-end="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform rotate-0 scale-100"
                        x-transition:leave-end="opacity-0 transform rotate-90 scale-50" class="w-5 h-5"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>
                <button @click="open = ! open" aria-label="Toggle navigation"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @hasanyrole('danru|anggota|backup')
                <x-responsive-nav-link :href="route('attendances.create')" :active="request()->routeIs('attendances.create')">
                    {{ __('Buat Absensi') }}
                </x-responsive-nav-link>
            @endhasanyrole
            @hasanyrole('superadmin|manajemen|danru')
                <x-responsive-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">
                    {{ __('Daftar Absensi') }}
                </x-responsive-nav-link>
            @endhasanyrole
            @can('create', App\Models\LeaveRequest::class)
                <x-responsive-nav-link :href="route('leave-requests.create')" :active="request()->routeIs('leave-requests.create')">
                    {{ __('Pengajuan Izin') }}
                </x-responsive-nav-link>
            @endcan
            <x-responsive-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.index')">
                {{ __('Daftar Pengajuan Izin') }}
            </x-responsive-nav-link>
            @can('viewAny', App\Models\Report::class)
                <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    {{ __('Laporan') }}
                </x-responsive-nav-link>
            @endcan
            @hasanyrole('superadmin|danru')
                <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Manajemen') }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        @role('superadmin')
                            <x-responsive-nav-link :href="route('report-types.index')" :active="request()->routeIs('report-types.*')">
                                {{ __('Manajemen Jenis Laporan') }}
                            </x-responsive-nav-link>
                        @endrole
                        <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('Manajemen Pengguna') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                            {{ __('Pengumuman') }}
                        </x-responsive-nav-link>
                        @role('superadmin')
                            <x-responsive-nav-link :href="route('role-permissions.index')" :active="request()->routeIs('role-permissions.*')">
                                {{ __('Manajemen Hak Akses') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('settings.location')" :active="request()->routeIs('settings.location')">
                                {{ __('Pengaturan Lokasi') }}
                            </x-responsive-nav-link>
                        @endrole
                    </div>
                </div>
            @endhasanyrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
