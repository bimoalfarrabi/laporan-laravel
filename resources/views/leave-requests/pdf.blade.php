<!DOCTYPE html>
<html>

<head>
    <title>Surat Pengajuan Izin</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .content {
            margin-bottom: 20px;
        }

        .content p {
            line-height: 1.6;
            margin: 0 0 5px 0;
        }

        .details-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .signatures {
            margin-top: 50px;
            width: 100%;
        }

        .signatures-left {
            width: 45%;
            float: left;
            text-align: center;
        }

        .signatures-right {
            width: 45%;
            float: right;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 60px auto 0 auto;
        }

        .clear {
            clear: both;
        }

        /* Emulate non-breaking space for indentation */
        .indent-2 {
            margin-left: 2em;
        }

        .indent-4 {
            margin-left: 4em;
        }

        .inline-block {
            display: inline-block;
            width: 70px;
        }

        /* Adjust width as needed for alignment */
    </style>
</head>

<body>
    <div class="container">
        <p>Banyuwangi, {{ \Carbon\Carbon::parse($leaveRequest->created_at)->format('d F Y') }}</p>
        <p>Kepada Yth.</p>
        <p>PT Wira Buana Arum</p>
        <p>Di Tempat.</p>

        <p style="margin-top: 20px;">Dengan hormat,</p>
        <p>Saya yang bertanda tangan di bawah ini:</p>

        <table class="details-table" style="margin-left: 2em;">
            <tr>
                <td style="width: 100px;">Nama</td>
                <td>: {{ $leaveRequest->user->name }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: {{ ucfirst($leaveRequest->user->roles->first()->name ?? 'Karyawan') }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>: {{ $leaveRequest->user->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penempatan</td>
                <td>: RSUD Blambangan</td>
            </tr>
        </table>

        @if ($leaveRequest->leave_type == 'Izin terlambat')
            <p>Dengan ini mengajukan permohonan izin terlambat dari pukul
                {{ \Carbon\Carbon::parse($leaveRequest->start_time)->format('H:i') }} hingga pukul
                {{ \Carbon\Carbon::parse($leaveRequest->end_time)->format('H:i') }} dengan keterangan
                {{ $leaveRequest->keterangan }}.</p>
        @else
            <p>Dengan ini mengajukan permohonan izin tidak masuk kerja selama
                {{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} hari, pada tanggal
                {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d F Y') }} sampai dengan tanggal
                {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d F Y') }} karena
                {{ Str::after($leaveRequest->leave_type, 'Izin ') }}.</p>
        @endif

        @if ($leaveRequest->leave_type == 'Izin terlambat')
            <p style="margin-top: 15px;">Saya akan segera melaksanakan tugas dan tanggung jawab saya setibanya di tempat
                kerja.</p>
        @else
            <p style="margin-top: 15px;">Saya berkomitmen untuk kembali bekerja setelah izin saya habis serta memastikan
                seluruh tugas dan tanggung jawab tetap berjalan dengan baik.</p>
        @endif

        <p style="margin-top: 15px;">Demikian surat izin ini saya sampaikan. Atas perhatian dan kebijaksanaannya, saya
            ucapkan terima kasih.</p>

        <div class="signatures">
            <div class="signatures-left">
                <p>Mengetahui</p>
                @if ($leaveRequest->approvedBy)
                    <div style="height: 10px;"></div>
                    <img
                        src="data:image/png;base64,{{ base64_encode(QrCode::size(80)->generate($leaveRequest->approvedBy->nik ?? '')) }}">
                    <p><strong>{{ $leaveRequest->approvedBy->name }}</strong></p>
                    <p>{{ ucfirst($leaveRequest->approvedBy->roles->first()->name ?? 'Atasan') }}</p>
                @else
                    <div style="height: 80px;"></div> {{-- Spacer for signature --}}
                    <p><strong>(_________________)</strong></p>
                @endif
            </div>
            <div class="signatures-right">
                <p>Hormat saya,</p>
                <div style="height: 10px;"></div>
                <img
                    src="data:image/png;base64,{{ base64_encode(QrCode::size(80)->generate($leaveRequest->user->nik ?? '')) }}">
                <p><strong>{{ $leaveRequest->user->name }}</strong></p>
                <p>{{ ucfirst($leaveRequest->user->roles->first()->name ?? 'Karyawan') }}</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>

</html>
