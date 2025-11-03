<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Support\Str;

class ReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user to associate with the creation of the report types
        $user = User::first(); // Assumes at least one user exists
        if (!$user) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        $reportTypes = [
            [
                'name' => 'Laporan Harian Jaga (LHJ) / Shift Report',
                'description' => 'Ringkasan kegiatan selama shift: jadwal, personel, pos jaga, cuaca, kejadian menonjol, serah-terima.',
                'fields' => [
                    ['label' => 'Jadwal Shift', 'name' => 'jadwal_shift', 'type' => 'text', 'required' => true],
                    ['label' => 'Personel Hadir', 'name' => 'personel_hadir', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Pos Jaga', 'name' => 'pos_jaga', 'type' => 'text', 'required' => true],
                    ['label' => 'Cuaca', 'name' => 'cuaca', 'type' => 'text', 'required' => true],
                    ['label' => 'Kejadian Menonjol', 'name' => 'kejadian_menonjol', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Serah Terima Tugas', 'name' => 'serah_terima_tugas', 'type' => 'textarea', 'required' => true],
                ]
            ],
            [
                'name' => 'Laporan Patroli',
                'description' => 'Rute/rounder, waktu cek tiap titik, temuan (pintu/alat/area), tindakan korektif, bukti foto bila ada.',
                'fields' => [
                    ['label' => 'Rute/Rounder Patroli', 'name' => 'rute_patroli', 'type' => 'text', 'required' => true],
                    ['label' => 'Waktu Cek Titik 1', 'name' => 'waktu_cek_1', 'type' => 'time', 'required' => true],
                    ['label' => 'Temuan di Titik 1', 'name' => 'temuan_1', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Tindakan Korektif', 'name' => 'tindakan_korektif', 'type' => 'textarea', 'required' => false],
                ]
            ],
            [
                'name' => 'Laporan Kejadian/Insiden',
                'description' => 'Kronologi lengkap (5W+1H), saksi, kerugian/cedera, tindakan awal, rekomendasi pencegahan, pelapor & penanggung jawab.',
                'fields' => [
                    ['label' => 'Saksi', 'name' => 'saksi', 'type' => 'text', 'required' => false],
                    ['label' => 'Kerugian/Cedera', 'name' => 'kerugian_cedera', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Tindakan Awal', 'name' => 'tindakan_awal', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Rekomendasi Pencegahan', 'name' => 'rekomendasi_pencegahan', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Penanggung Jawab', 'name' => 'penanggung_jawab', 'type' => 'text', 'required' => true],
                ]
            ],
            [
                'name' => 'Buku Tamu / Visitor Log',
                'description' => 'Nama, instansi, tujuan, PIC yang dikunjungi, waktu masuk-keluar, ID/kartu, barang yang dibawa.',
                'fields' => [
                    ['label' => 'Nama Tamu', 'name' => 'nama_tamu', 'type' => 'text', 'required' => true],
                    ['label' => 'Instansi', 'name' => 'instansi', 'type' => 'text', 'required' => false],
                    ['label' => 'Tujuan', 'name' => 'tujuan', 'type' => 'textarea', 'required' => true],
                    ['label' => 'PIC yang Dikunjungi', 'name' => 'pic_dikunjungi', 'type' => 'text', 'required' => true],
                    ['label' => 'Waktu Keluar', 'name' => 'waktu_keluar', 'type' => 'time', 'required' => false],
                    ['label' => 'Nomor ID/Kartu', 'name' => 'nomor_id', 'type' => 'text', 'required' => true],
                    ['label' => 'Barang yang Dibawa', 'name' => 'barang_dibawa', 'type' => 'textarea', 'required' => false],
                ]
            ],
            [
                'name' => 'Laporan Kendaraan & Barang Keluar-Masuk',
                'description' => 'Plat nomor, jenis kendaraan, sopir, muatan, dokumen (SJ/DO), waktu, pemeriksaan fisik.',
                'fields' => [
                    ['label' => 'Plat Nomor', 'name' => 'plat_nomor', 'type' => 'text', 'required' => true],
                    ['label' => 'Jenis Kendaraan', 'name' => 'jenis_kendaraan', 'type' => 'text', 'required' => true],
                    ['label' => 'Nama Sopir', 'name' => 'nama_sopir', 'type' => 'text', 'required' => true],
                    ['label' => 'Muatan', 'name' => 'muatan', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Nomor Dokumen (SJ/DO)', 'name' => 'nomor_dokumen', 'type' => 'text', 'required' => false],
                    ['label' => 'Hasil Pemeriksaan Fisik', 'name' => 'pemeriksaan_fisik', 'type' => 'textarea', 'required' => true],
                ]
            ],
            [
                'name' => 'Laporan Temuan & Kehilangan (Lost & Found)',
                'description' => 'Deskripsi barang, lokasi & waktu ditemukan/hilang, penemu/pemilik, penyerahan/penyimpanan.',
                'fields' => [
                    ['label' => 'Lokasi Ditemukan/Hilang', 'name' => 'lokasi', 'type' => 'text', 'required' => true],
                    ['label' => 'Nama Penemu/Pemilik', 'name' => 'penemu_pemilik', 'type' => 'text', 'required' => true],
                    ['label' => 'Info Penyerahan/Penyimpanan', 'name' => 'info_penyerahan', 'type' => 'textarea', 'required' => true],
                ]
            ],
            [
                'name' => 'Laporan Kerusakan/Sarana & Prasarana',
                'description' => 'Unit/alat terdampak (CCTV, panel, penerangan), gejala, lokasi, waktu, tindak lanjut/eskalasi.',
                'fields' => [
                    ['label' => 'Unit/Alat Terdampak', 'name' => 'unit_alat', 'type' => 'text', 'required' => true],
                    ['label' => 'Gejala Kerusakan', 'name' => 'gejala', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Lokasi', 'name' => 'lokasi', 'type' => 'text', 'required' => true],
                    ['label' => 'Tindak Lanjut/Eskalasi', 'name' => 'tindak_lanjut', 'type' => 'textarea', 'required' => true],
                ]
            ],
            [
                'name' => 'Laporan Pelanggaran/Kedisiplinan',
                'description' => 'Jenis pelanggaran (akses, APD, merokok area terlarang, dll.), pelaku, bukti, sanksi/edukasi.',
                'fields' => [
                    ['label' => 'Jenis Pelanggaran', 'name' => 'jenis_pelanggaran', 'type' => 'text', 'required' => true],
                    ['label' => 'Nama Pelaku', 'name' => 'nama_pelaku', 'type' => 'text', 'required' => true],
                    ['label' => 'Sanksi/Edukasi yang Diberikan', 'name' => 'sanksi_edukasi', 'type' => 'textarea', 'required' => false],
                ]
            ],
            [
                'name' => 'Laporan Keamanan Khusus / Keadaan Darurat',
                'description' => 'Kebakaran, kebocoran, bencana, ancaman; status evakuasi, koordinasi dengan pihak eksternal (Damkar/Polisi/Medis).',
                'fields' => [
                    ['label' => 'Jenis Keadaan Darurat', 'name' => 'jenis_darurat', 'type' => 'text', 'required' => true],
                    ['label' => 'Status Evakuasi', 'name' => 'status_evakuasi', 'type' => 'text', 'required' => true],
                    ['label' => 'Koordinasi Pihak Eksternal', 'name' => 'koordinasi_eksternal', 'type' => 'textarea', 'required' => false],
                ]
            ],
            [
                'name' => 'Laporan Pengawalan / Pengamanan Kegiatan',
                'description' => 'Nama event, penanggung jawab, rencana pengamanan, jumlah personel, jalannya kegiatan, evaluasi.',
                'fields' => [
                    ['label' => 'Nama Event', 'name' => 'nama_event', 'type' => 'text', 'required' => true],
                    ['label' => 'Penanggung Jawab Event', 'name' => 'penanggung_jawab_event', 'type' => 'text', 'required' => true],
                    ['label' => 'Rencana Pengamanan', 'name' => 'rencana_pengamanan', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Jumlah Personel', 'name' => 'jumlah_personel', 'type' => 'number', 'required' => true],
                    ['label' => 'Jalannya Kegiatan', 'name' => 'jalannya_kegiatan', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Evaluasi', 'name' => 'evaluasi', 'type' => 'textarea', 'required' => false],
                ]
            ],
        ];

        foreach ($reportTypes as $typeData) {
            // Create or update the Report Type
            $reportType = ReportType::updateOrCreate(
                ['name' => $typeData['name']],
                [
                    'slug' => Str::slug($typeData['name']),
                    'description' => $typeData['description'],
                    'is_active' => true,
                    'created_by_user_id' => $user->id,
                    'updated_by_user_id' => $user->id,
                ]
            );

            // --- Default Fields ---
            $defaultFields = [
                ['label' => 'Deskripsi', 'name' => 'deskripsi', 'type' => 'textarea', 'required' => true, 'order' => 1],
                ['label' => 'Tanggal', 'name' => 'tanggal', 'type' => 'date', 'required' => true, 'order' => 2],
                ['label' => 'Waktu', 'name' => 'waktu', 'type' => 'time', 'required' => true, 'order' => 3],
                ['label' => 'Upload Gambar', 'name' => 'upload_gambar', 'type' => 'file', 'required' => false, 'order' => 4],
            ];

            foreach ($defaultFields as $field) {
                $reportType->reportTypeFields()->updateOrCreate(
                    ['name' => $field['name'], 'report_type_id' => $reportType->id],
                    $field
                );
            }

            // --- Custom Fields from Explanation ---
            $order = 5; // Start order after default fields
            foreach ($typeData['fields'] as $field) {
                $field['order'] = $order++;
                $reportType->reportTypeFields()->updateOrCreate(
                    ['name' => $field['name'], 'report_type_id' => $reportType->id],
                    $field
                );
            }
        }
    }
}