@if ($leaveRequests->isEmpty())
    <div class="text-center py-10">
        <p class="text-gray-500 dark:text-gray-400">Tidak ada pengajuan izin yang ditemukan.</p>
    </div>
@else
    {{-- Table View for Larger Screens --}}
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    @php
                        $columns = [
                            'user_name' => 'Pemohon',
                            'leave_type' => 'Jenis Izin',
                            'start_date' => 'Tanggal',
                            'status' => 'Status',
                        ];
                        $currentSortBy = request('sort_by', 'created_at');
                        $currentSortOrder = request('sort_order', 'desc');
                    @endphp

                    @foreach ($columns as $field => $label)
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer group">
                            @php
                                $nextSortOrder =
                                    $currentSortBy === $field && $currentSortOrder === 'asc' ? 'desc' : 'asc';
                                $url = request()->fullUrlWithQuery([
                                    'sort_by' => $field,
                                    'sort_order' => $nextSortOrder,
                                ]);
                            @endphp
                            <a href="{{ $url }}"
                                class="flex items-center group-hover:text-gray-700 dark:group-hover:text-gray-200 sort-link"
                                data-sort-by="{{ $field }}" data-sort-order="{{ $nextSortOrder }}">
                                {{ $label }}
                                @if ($currentSortBy === $field)
                                    <span class="ml-1">
                                        @if ($currentSortOrder === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                @else
                                    <span class="ml-1 text-gray-400 opacity-0 group-hover:opacity-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </span>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($leaveRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $request->user->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $request->user->roles->first()->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $request->leave_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $request->start_date->diffInDays($request->end_date) + 1 }} hari</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = '';
                                if ($request->status == 'menunggu persetujuan') {
                                    $statusClass = 'bg-yellow-200 text-yellow-800';
                                } elseif ($request->status == 'disetujui') {
                                    $statusClass = 'bg-green-200 text-green-800';
                                } elseif ($request->status == 'ditolak') {
                                    $statusClass = 'bg-red-200 text-red-800';
                                }
                            @endphp
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('leave-requests.show', $request->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Card View for Small Screens --}}
    <div class="mt-6 sm:hidden space-y-4">
        @foreach ($leaveRequests as $request)
            <div class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $request->user->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->leave_type }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}
                        </div>
                    </div>
                    @php
                        $statusClass = '';
                        if ($request->status == 'menunggu persetujuan') {
                            $statusClass = 'bg-yellow-200 text-yellow-800';
                        } elseif ($request->status == 'disetujui') {
                            $statusClass = 'bg-green-200 text-green-800';
                        } elseif ($request->status == 'ditolak') {
                            $statusClass = 'bg-red-200 text-red-800';
                        }
                    @endphp
                    <span
                        class="px-2 py-1 text-xs leading-5 font-semibold rounded-full {{ $statusClass }} flex-shrink-0">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>
                <div class="mt-2 border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-end">
                    <a href="{{ route('leave-requests.show', $request->id) }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat
                        Detail</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $leaveRequests->links('pagination.custom') }}
    </div>
@endif
