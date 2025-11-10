<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Export Laporan Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4 p-4 bg-gray-50 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Pilih Bulan dan Tahun</h3>
                        <p class="text-sm text-gray-600 mb-4">Pilih bulan dan tahun untuk men-download laporan bulanan dalam format PDF.</p>
                        <form action="{{ route('reports.exportMonthlyPdf', ['year' => 'YEAR_PLACEHOLDER', 'month' => 'MONTH_PLACEHOLDER']) }}" method="GET" id="monthlyExportForm">
                            <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                <select name="month" id="exportMonth" class="block w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" id="exportYear" class="block w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach (range(date('Y'), date('Y') - 5) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                                <x-primary-button type="submit" class="w-full sm:w-auto">
                                    Export PDF Bulanan
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                    <script>
                        document.getElementById('monthlyExportForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            const month = document.getElementById('exportMonth').value;
                            const year = document.getElementById('exportYear').value;
                            let url = this.action.replace('YEAR_PLACEHOLDER', year).replace('MONTH_PLACEHOLDER', month);
                            window.location.href = url;
                        });
                    </script>

                    <div class="mt-6">
                        <a href="{{ route('reports.index') }}" class="text-blue-600 hover:underline">
                            &larr; Kembali ke Daftar Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
