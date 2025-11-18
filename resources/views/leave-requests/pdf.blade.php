<!DOCTYPE html>
<html>
<head>
    <title>Surat Pengajuan Izin</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; padding: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 0; font-size: 14px; }
        .content { margin-bottom: 40px; }
        .content p { line-height: 1.6; }
        .details-table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        .details-table td { padding: 8px 0; }
        .signatures { margin-top: 80px; width: 100%; }
        .signatures td { width: 50%; text-align: center; padding: 20px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 60px auto 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SURAT PENGAJUAN IZIN</h1>
        </div>

        <div class="content">
            <p>Dengan hormat,</p>
            <p>Saya yang bertanda tangan di bawah ini:</p>
            <table class="details-table">
                <tr>
                    <td style="width: 150px;">Nama</td>
                    <td>: {{ $leaveRequest->user->name }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>: {{ ucfirst($leaveRequest->user->roles->first()->name ?? 'Karyawan') }}</td>
                </tr>
                <tr>
                    <td>Jenis Izin</td>
                    <td>: {{ $leaveRequest->leave_type }}</td>
                </tr>
            </table>
            <p>
                Dengan ini mengajukan permohonan izin selama {{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} hari, terhitung mulai tanggal 
                <strong>{{ $leaveRequest->start_date->format('d F Y') }}</strong> sampai dengan tanggal <strong>{{ $leaveRequest->end_date->format('d F Y') }}</strong>.
            </p>
            <p>Adapun alasan saya mengajukan izin adalah berikut:</p>
            <p><em>{{ $leaveRequest->reason }}</em></p>
            <p>Demikian surat permohonan izin ini saya buat dengan sebenarnya. Atas perhatian dan izin yang diberikan, saya ucapkan terima kasih.</p>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <p>Hormat saya,</p>
                    <div class="signature-line"></div>
                    <p><strong>{{ $leaveRequest->user->name }}</strong></p>
                </td>
                <td>
                    <p>Menyetujui,</p>
                    <div class="signature-line"></div>
                    <p><strong>{{ $leaveRequest->approvedBy->name ?? '(_________________)' }}</strong></p>
                    <p>{{ $leaveRequest->approvedBy ? ucfirst($leaveRequest->approvedBy->roles->first()->name ?? 'Atasan') : '' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
