<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jenis-jenis Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Penjelasan Jenis Laporan</h3>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Harian Jaga (LHJ) / Shift Report</h4>
                        <p class="text-sm text-gray-600">Ringkasan kegiatan selama shift: jadwal, personel, pos jaga, cuaca, kejadian menonjol, serah-terima.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Patroli</h4>
                        <p class="text-sm text-gray-600">Rute/rounder, waktu cek tiap titik, temuan (pintu/alat/area), tindakan korektif, bukti foto bila ada.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Kejadian/Insiden</h4>
                        <p class="text-sm text-gray-600">Kronologi lengkap (5W+1H), saksi, kerugian/cedera, tindakan awal, rekomendasi pencegahan, pelapor & penanggung jawab.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Buku Tamu / Visitor Log</h4>
                        <p class="text-sm text-gray-600">Nama, instansi, tujuan, PIC yang dikunjungi, waktu masuk-keluar, ID/kartu, barang yang dibawa.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Kendaraan & Barang Keluar-Masuk</h4>
                        <p class="text-sm text-gray-600">Plat nomor, jenis kendaraan, sopir, muatan, dokumen (SJ/DO), waktu, pemeriksaan fisik.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Temuan & Kehilangan (Lost & Found)</h4>
                        <p class="text-sm text-gray-600">Deskripsi barang, lokasi & waktu ditemukan/hilang, penemu/pemilik, penyerahan/penyimpanan.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Kerusakan/Sarana & Prasarana</h4>
                        <p class="text-sm text-gray-600">Unit/alat terdampak (CCTV, panel, penerangan), gejala, lokasi, waktu, tindak lanjut/eskalasi.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Pelanggaran/Kedisiplinan</h4>
                        <p class="text-sm text-gray-600">Jenis pelanggaran (akses, APD, merokok area terlarang, dll.), pelaku, bukti, sanksi/edukasi.</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Keamanan Khusus / Keadaan Darurat</h4>
                        <p class="text-sm text-gray-600">Kebakaran, kebocoran, bencana, ancaman; status evakuasi, koordinasi dengan pihak eksternal (Damkar/Polisi/Medis).</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold">Laporan Pengawalan / Pengamanan Kegiatan</h4>
                        <p class="text-sm text-gray-600">Nama event, penanggung jawab, rencana pengamanan, jumlah personel, jalannya kegiatan, evaluasi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>