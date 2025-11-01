<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{ $report->reportType->name }}</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 800px; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin-top: 20px; }
        .report-data { border-collapse: collapse; width: 100%; }
        .report-data th, .report-data td { border: 1px solid #ddd; padding: 8px; }
        .report-data th { background-color: #f2f2f2; text-align: left; }
        .metadata { width: 100%; margin-bottom: 20px; }
        .metadata td { padding: 5px; }
        .img-fluid { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detail Laporan</h1>
        </div>

        <table class="metadata">
            <tr>
                <td><strong>ID Laporan:</strong></td>
                <td>{{ $report->id }}</td>
            </tr>
            <tr>
                <td><strong>Jenis Laporan:</strong></td>
                <td>{{ $report->reportType->name }}</td>
            </tr>
            <tr>
                <td><strong>Dibuat Oleh:</strong></td>
                <td>{{ $report->user->name }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ ucfirst($report->status) }}</td>
            </tr>
            <tr>
                <td><strong>Dibuat Pada:</strong></td>
                <td>{{ $report->created_at->format('d-m-Y H:i') }}</td>
            </tr>
        </table>

        <div class="content">
            <h2>Data Laporan</h2>
            <table class="report-data">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report->reportType->reportTypeFields as $field)
                        <tr>
                            <td><strong>{{ $field->label }}</strong></td>
                            <td>
                                @if ($field->type === 'textarea' || $field->type === 'text')
                                    <p style="white-space: pre-wrap;">{{ $report->data[$field->name] ?? '-' }}</p>
                                @elseif ($field->type === 'date')
                                    {{ isset($report->data[$field->name]) ? \Carbon\Carbon::parse($report->data[$field->name])->format('d-m-Y') : '-' }}
                                @elseif ($field->type === 'time')
                                    {{ $report->data[$field->name] ?? '-' }}
                                @elseif ($field->type === 'checkbox')
                                    {{ ($report->data[$field->name] ?? false) ? 'Ya' : 'Tidak' }}
                                @elseif ($field->type === 'file')
                                    @if (isset($report->data[$field->name]) && $report->data[$field->name])
                                        @php
                                            $imagePath = storage_path('app/public/' . $report->data[$field->name]);
                                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                                        @endphp
                                        @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg']) && file_exists($imagePath))
                                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="{{ $field->label }}" class="img-fluid">
                                        @else
                                            <p style="color: red;">foto telah dihapus</p>
                                        @endif
                                    @else
                                        -
                                    @endif
                                @else
                                    {{ $report->data[$field->name] ?? '-' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>