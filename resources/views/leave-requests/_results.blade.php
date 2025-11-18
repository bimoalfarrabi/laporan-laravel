@if ($leaveRequests->isEmpty())
    <div class="text-center py-10">
        <p class="text-gray-500">Tidak ada pengajuan izin yang ditemukan.</p>
    </div>
@else
    {{-- Table View for Larger Screens --}}
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($leaveRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $request->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $request->user->roles->first()->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->leave_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $request->start_date->diffInDays($request->end_date) + 1 }} hari</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = '';
                                if ($request->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                elseif ($request->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                elseif ($request->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('leave-requests.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Card View for Small Screens --}}
    <div class="mt-6 sm:hidden space-y-4">
        @foreach ($leaveRequests as $request)
            <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-gray-800">{{ $request->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $request->leave_type }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</div>
                    </div>
                    @php
                        $statusClass = '';
                        if ($request->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                        elseif ($request->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                        elseif ($request->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                    @endphp
                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full {{ $statusClass }} flex-shrink-0">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>
                <div class="mt-2 border-t pt-2 flex justify-end">
                    <a href="{{ route('leave-requests.show', $request->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Lihat Detail</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>
@endif
