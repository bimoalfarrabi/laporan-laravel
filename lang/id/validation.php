<?php

return [
    'required' => 'Kolom :attribute wajib diisi.',
    'string'   => 'Kolom :attribute harus berupa teks.',
    'max'      => [
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'date'       => 'Kolom :attribute bukan tanggal yang valid.',
    'after'      => 'Kolom :attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => 'Kolom :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'nullable'   => 'Kolom :attribute boleh kosong.',

    'attributes' => [
        'title' => 'Judul',
        'content' => 'Isi Pengumuman',
        'starts_at' => 'Tanggal Mulai',
        'expires_at' => 'Tanggal Kedaluwarsa',
    ],
];
