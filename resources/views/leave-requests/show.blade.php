<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Pengajuan Izin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Metadata --}}
                    <div class="space-y-4 mb-6">
                        <p><strong>Pemohon:</strong> {{ $leaveRequest->user->name }}</p>
                        <p><strong>Jenis Izin:</strong> {{ $leaveRequest->leave_type }}</p>
                        <p><strong>Tanggal:</strong> {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }} ({{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} hari)</p>
                        <p><strong>Alasan:</strong></p>
                        <div class="prose max-w-none p-4 bg-gray-50 rounded-md border border-gray-200">
                            {{ $leaveRequest->reason }}
                        </div>
                        <p><strong>Status:</strong>
                            @php
                                $statusClass = '';
                                if ($leaveRequest->status == 'menunggu persetujui') $statusClass = 'bg-yellow-200 text-yellow-800';
                                elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        </p>
                    </div>

                    {{-- Signature/History Block --}}
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Riwayat Pengajuan</h3>
                        <div class="text-base text-gray-700 space-y-3">
                            <p><strong>Dibuat oleh:</strong> {{ $leaveRequest->user->name }} pada <span class="font-medium">{{ $leaveRequest->created_at->format('d-m-Y H:i') }}</span></p>
                            @if ($leaveRequest->status === 'disetujui')
                                <p><strong>Disetujui oleh:</strong> {{ $leaveRequest->approvedBy->name }} pada <span class="font-medium">{{ $leaveRequest->approved_at->format('d-m-Y H:i') }}</span></p>
                            @elseif ($leaveRequest->status === 'ditolak')
                                <p><strong>Ditolak oleh:</strong> {{ $leaveRequest->rejectedBy->name }} pada <span class="font-medium">{{ $leaveRequest->rejected_at->format('d-m-Y H:i') }}</span></p>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-8 pt-6 border-t">
                        <div class="flex items-center justify-end gap-4">
                            @can('approveOrReject', $leaveRequest)
                                @if ($leaveRequest->status === 'menunggu persetujuan')
                                    <form action="{{ route('leave-requests.approve', $leaveRequest->id) }}" method="POST">
                                        @csrf
                                        <x-primary-button class="bg-green-600 hover:bg-green-500">
                                            {{ __('Setujui') }}
                                        </x-primary-button>
                                    </form>
                                    <form action="{{ route('leave-requests.reject', $leaveRequest->id) }}" method="POST">
                                        @csrf
                                        <x-danger-button>
                                            {{ __('Tolak') }}
                                        </x-danger-button>
                                    </form>
                                @endif
                            @endcan

                            @can('exportPdf', $leaveRequest)
                                <a href="{{ route('leave-requests.exportPdf', $leaveRequest->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500">
                                    {{ __('Export PDF') }}
                                </a>
                            @endcan

                            <x-secondary-button type="button" onclick="window.history.back()">
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
