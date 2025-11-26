<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail Laporan: ') . $report->reportType->name }}
            </h2>
            <button id="share-button"
                class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Share') }}
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

                    {{-- Navigation Buttons --}}
                    <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                        @if ($previousReport)
                            <a href="{{ route('reports.show', $previousReport->id) }}"
                                class="nav-report-btn text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center py-2 px-4 border border-indigo-200 dark:border-indigo-700 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                data-report-id="{{ $previousReport->id }}">
                                <svg class="w-4 h-4 mr-1 md:mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                                <span class="hidden md:inline">Laporan Sebelumnya</span>
                            </a>
                        @else
                            <span class="hidden md:inline-block"></span> {{-- Placeholder to maintain space on larger screens --}}
                        @endif

                        @if ($nextReport)
                            <a href="{{ route('reports.show', $nextReport->id) }}"
                                class="nav-report-btn text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center py-2 px-4 border border-indigo-200 dark:border-indigo-700 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                data-report-id="{{ $nextReport->id }}">
                                <span class="hidden md:inline">Laporan Berikutnya</span>
                                <svg class="w-4 h-4 ml-1 md:ml-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span class="hidden md:inline-block"></span> {{-- Placeholder to maintain space on larger screens --}}
                        @endif
                    </div>

                    @include('reports.partials.report_details', [
                        'report' => $report,
                        'previousReport' => $previousReport,
                        'nextReport' => $nextReport,
                    ])

                    {{-- Action Buttons --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-start sm:justify-end gap-4">
                            @if ($report->deleted_at)
                                @can('restore', $report)
                                    <form action="{{ route('reports.restore', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            onclick="return confirm('Apakah Anda yakin ingin memulihkan laporan ini?')">
                                            {{ __('Pulihkan') }}
                                        </button>
                                    </form>
                                @endcan
                                @can('forceDelete', $report)
                                    <form action="{{ route('reports.forceDelete', $report->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            onclick="return confirm('PERINGATAN: Ini akan menghapus laporan secara PERMANEN. Apakah Anda yakin?')">
                                            {{ __('Hapus Permanen') }}
                                        </button>
                                    </form>
                                @endcan
                            @else
                                @if (
                                    $report->status == 'belum disetujui' &&
                                        (Auth::user()->can('reports:approve') || Auth::user()->can('reports:reject')) &&
                                        Auth::id() !== $report->user_id)
                                    @can('approve', $report)
                                        <form action="{{ route('reports.approve', $report->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('Setujui') }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('reject', $report)
                                        <form action="{{ route('reports.reject', $report->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('Tolak') }}
                                            </button>
                                        </form>
                                    @endcan
                                @endif

                                @can('update', $report)
                                    <a href="{{ route('reports.edit', $report->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Edit') }}
                                    </a>
                                @endcan
                                {{-- @can('view', $report)
                                    <a href="{{ route('reports.exportPdf', $report->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Export PDF') }}
                                    </a>
                                @endcan --}}
                            @endif
                            <a href="{{ route('reports.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Kembali') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-image-modal />
    <x-video-modal />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shareButton = document.getElementById('share-button');
            if (shareButton) {
                shareButton.addEventListener('click', async () => {
                    const reportUrl = window.location.href;
                    const reportTitle = 'Laporan';
                    const reportText = 'Lihat detail laporan:';

                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: reportTitle,
                                text: reportText,
                                url: reportUrl,
                            });
                        } catch (error) {
                            // Ignore abort errors
                            if (error.name !== 'AbortError') {
                                console.error('Error sharing:', error);
                            }
                        }
                    } else {
                        try {
                            await navigator.clipboard.writeText(reportUrl);
                            alert('Link laporan telah disalin ke clipboard!');
                        } catch (error) {
                            console.error('Error copying to clipboard:', error);
                            alert('Gagal menyalin link.');
                        }
                    }
                });
            }
        });
    </script>

</x-app-layout>
