@extends('layouts.appikom')

@section('title', '- Laporan SIG')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/stylekehadiran.css') }}?v={{ filemtime(public_path('css/stylekehadiran.css')) }}">

<link rel="stylesheet" href="{{ asset('css/stylelaporanSIG.css') }}?v={{ file_exists(public_path('css/stylelaporanSIG.css')) ? filemtime(public_path('css/stylelaporanSIG.css')) : '' }}">

<div class="kehadiran-wrapper">
    <div class="list-header view-laporan-header">
        @php 
            $logoMaps = [
                'Intelligence Machines Club' => 'imachine.png',
                'CyberHack & Ethic' => 'cyber.png',
                'Inovasi Bisnes' => 'ibisnes.png',
                'Interactive Multimedia Club' => 'imec.png',
                'Mobile Application Development Club' => 'mad.png',
                'Autonomous Robot and Vision Systems' => 'arvis.png',
                'Programming Club' => 'pc.png',
                'Video Innovation Club' => 'vic.png',
            ];
            $logoFile = $logoMaps[$sig->fld_sig_nama] ?? 'imachine.png';
        @endphp
        <div class="view-laporan-sig-info">
            <div class="sig-logo-frame">
                <img src="{{ asset('pic/logoSIG/' . $logoFile) }}" alt="{{ $sig->fld_sig_nama }} Logo" class="sig-logo">
            </div>
            <div class="sig-text-info">
                <h1>Laporan Kursus</h1>
                <p>{{ $sig->fld_sig_nama }}</p>
            </div>
        </div>
        <div class="view-laporan-header-actions">
            <a href="{{ route('laporanSIG.export', $sig->fld_sig_id) }}" class="btn-submit btn-view-export">
                <i class="fas fa-file-excel"></i> Muat Turun
            </a>
            <a href="{{ route('laporanSIG') }}" class="btn-reset btn-view-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="glass-effect">
        <div class="laporan-table-wrapper">
            <table class="custom-table laporan-table">
                <thead>
                    <tr>
                        <th>No. Matrik</th>
                        <th>Nama Pelajar</th>
                        <th>Tahun</th>
                        <th>Jurusan</th>
                        @foreach($kriterias as $k)
                            <th>{{ $k->fld_krit_nama }} ({{ $k->fld_krit_markah }}%)</th>
                        @endforeach
                        <th>Markah Keseluruhan</th>
                        <th>Gred</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelajars as $pelajar)
                        @php
                            $studentMarks = $pelajar->penilaian->keyBy('fld_krit_id');
                            $keputusan = $keputusans->get($pelajar->fld_pel_nomat);
                        @endphp
                        <tr>
                            <td>{{ $pelajar->fld_pel_nomat }}</td>
                            <td>{{ $pelajar->pengguna->fld_user_nama ?? '-' }}</td>
                            <td>{{ $pelajar->fld_pel_tahun }}</td>
                            <td>{{ $pelajar->fld_pel_jurusan }}</td>
                            @foreach($kriterias as $k)
                                @php
                                    $mark = $studentMarks->get($k->fld_krit_id);
                                @endphp
                                <td>{{ $mark ? number_format($mark->fld_nilai_markah, 2) : '0.00' }}</td>
                            @endforeach
                            <td>
                                <strong class="text-markah-keseluruhan">{{ $keputusan ? number_format($keputusan->fld_total_markah, 2) . '%' : '0.00%' }}</strong>
                            </td>
                            <td>
                                @if($keputusan && $keputusan->fld_nilai_gred)
                                    <span class="status-badge badge-gred">
                                        {{ $keputusan->fld_nilai_gred }}
                                    </span>
                                @else
                                    <span class="text-muted-dash">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $kriterias->count() }}" class="empty-state-cell">
                                Tiada rekod pelajar untuk SIG ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
