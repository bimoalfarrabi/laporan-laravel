<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan Anggota</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-item {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ccc;
        }
        .report-item:last-child {
            border-bottom: none;
        }
        .field-label {
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Bulanan Anggota</h1>
        <h2>Bulan: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h2>
    </div>

    @if ($reports->isEmpty())
        <p>Tidak ada laporan anggota yang ditemukan untuk bulan ini.</p>
    @else
        @foreach ($reports as $report)
            <div class="report-item">
                <h3>{{ $report->reportType->name }} - Oleh: {{ $report->user->name }}</h3>
                <p><strong>Tanggal Laporan:</strong> {{ $report->created_at->format('d-m-Y H:i') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($report->status) }}</p>
                
                @foreach ($report->reportType->reportTypeFields as $field)
                    @if (isset($report->data[$field->name]))
                        <p class="field-label">{{ $field->label }}:</p>
                        @if ($field->type === 'textarea' || $field->type === 'text')
                            <p>{{ $report->data[$field->name] }}</p>
                        @elseif ($field->type === 'date')
                            <p>{{ Carbon\Carbon::parse($report->data[$field->name])->format('d-m-Y') }}</p>
                        @elseif ($field->type === 'time')
                            <p>{{ $report->data[$field->name] }}</p>
                        @elseif ($field->type === 'checkbox')
                            <p>{{ ($report->data[$field->name]) ? 'Ya' : 'Tidak' }}</p>
                        @elseif ($field->type === 'file')
                            <p>File: <a href="{{ Storage::url($report->data[$field->name]) }}">{{ basename($report->data[$field->name]) }}</a></p>
                        @else
                            <p>{{ $report->data[$field->name] }}</p>
                        @endif
                    @endif
                @endforeach
            </div>
            @if (!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif
</body>
</html>