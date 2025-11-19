<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Bulanan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px; /* Smaller font for table content */
        }
        th, td {
            border: 1px solid #777;
            padding: 2px; /* Further reduce padding */
            text-align: left;
            vertical-align: top;
        }
        .attendance-cell {
            min-width: 40px; /* Further reduce min-width */
        }
        .present {
            background-color: #e6ffed;
        }
        .leave {
            background-color: #fffbea;
            text-align: center;
        }
        .absent {
            background-color: #ffebeb;
            text-align: center;
        }
        .status {
             font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Absensi Bulanan</h1>
            <p>{{ $monthName }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    @foreach ($dateRange as $date)
                        <th class="date-header">{{ $date->format('d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dataMatrix as $userId => $userData)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td class="user-name">{{ $userData['user_name'] }}</td>
                        @foreach ($dateRange as $date)
                            @php
                                $dateString = $date->format('Y-m-d');
                                $dayData = $userData['dates'][$dateString] ?? null;
                            @endphp
                            <td class="attendance-cell
                                @if($dayData && $dayData['status'] === 'Hadir') present @endif
                                @if($dayData && $dayData['status'] === 'Izin') leave @endif
                                @if(!$dayData || $dayData['status'] === 'Tidak Hadir') absent @endif
                            ">
                                @if ($dayData)
                                    @if ($dayData['status'] === 'Hadir')
                                        <div><span class="status">Masuk:</span> <span class="{{ $dayData['is_late'] ? 'late' : '' }}">{{ $dayData['time_in'] }}</span></div>
                                        <div><span class="status">Pulang:</span> {{ $dayData['time_out'] }}</div>
                                        <div style="font-size: 7px; color: #555;">{{ $dayData['type'] }}</div>
                                    @elseif ($dayData['status'] === 'Izin')
                                        <div class="status">Izin</div>
                                        <div style="font-size: 7px; color: #555;">{{ $dayData['type'] }}</div>
                                    @else
                                        <div class="status">-</div>
                                    @endif
                                @else
                                    <div class="status">-</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
