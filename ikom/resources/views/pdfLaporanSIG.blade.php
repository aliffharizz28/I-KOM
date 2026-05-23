<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Markah Pelajar - {{ $sigName }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2, h3 { text-align: center; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Markah Pelajar</h2>
    <h3>SIG: {{ $sigName }}</h3>
    @if($sesiAktif)
        <h3 style="font-weight: normal; font-size: 14px;">Sesi Akademik: {{ $sesiAktif->fld_krs_tahun }} Semester {{ $sesiAktif->fld_krs_semester }}</h3>
    @endif

    <table>
        <thead>
            <tr>
                <th>No. Matrik</th>
                <th>Nama Pelajar</th>
                <th>Tahun</th>
                <th>Jurusan</th>
                <th class="text-center">Markah Keseluruhan</th>
                <th class="text-center">Gred</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelajars as $pelajar)
                @php
                    $keputusan = $keputusans->get($pelajar->fld_pel_nomat);
                @endphp
                <tr>
                    <td>{{ $pelajar->fld_pel_nomat }}</td>
                    <td>{{ $pelajar->pengguna->fld_user_nama ?? '-' }}</td>
                    <td>{{ $pelajar->fld_pel_tahun }}</td>
                    <td>{{ $pelajar->fld_pel_jurusan }}</td>
                    <td class="text-center">
                        <strong>{{ $keputusan ? number_format($keputusan->fld_total_markah, 2) . '%' : '0.00%' }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $keputusan && $keputusan->fld_nilai_gred ? $keputusan->fld_nilai_gred : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tiada rekod pelajar untuk SIG ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
