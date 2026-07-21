@extends('layouts.appikom')

@section('title', '- Papan Pemuka')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styledashboard.css') }}?v={{ filemtime(public_path('css/styledashboard.css')) }}">

@if (Auth::user()->fld_user_role == 1)
<div class="dashboard-wrapper">

    <!-- Stats Row -->
    <div class="info-container">
        <div class="info-box box-pelajar">
            <div class="box-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="box-data">
                <h3>Jumlah Pelajar</h3>
                <p class="big-number">{{ $totalPelajar }}</p>
            </div>
        </div>

        <div class="info-box box-kehadiran">
            <div class="box-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="box-data">
                <h3>SIG Dengan Penyelaras</h3>
                <p class="big-number">{{ $sigWithPenyelaras }} / {{ $totalSig }}</p>
            </div>
        </div>

        <div class="info-box box-lewat">
            <div class="box-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="box-data">
                <h3>Kursus Berdaftar</h3>
                <p class="big-number">{{ $totalKursus }}</p>
            </div>
        </div>
    </div>

    <!-- Alerts: Unassigned SIGs -->
    @if($unassignedSigs->count() > 0)
    <div class="admin-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{{ $unassignedSigs->count() }} SIG belum mempunyai Penyelaras:</strong>
            {{ $unassignedSigs->pluck('nama')->join(', ') }}
        </div>
    </div>
    @endif

    <!-- SIG Overview Table + Garis Masa -->
    <div class="dashboard-content-row">
        <div class="infoterkini-container" style="flex: 1;">
            <div class="card-header">
                <h2>Gambaran Keseluruhan SIG</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama SIG</th>
                            <th>Penyelaras</th>
                            <th>Bil. Pelajar</th>
                            <th>Dinilai</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sigs as $sig)
                        <tr>
                            <td class="sig-name-cell">{{ $sig['nama'] }}</td>
                            <td>
                                @if($sig['penyelaras'])
                                    <span class="status-assigned">{{ $sig['penyelaras'] }}</span>
                                @else
                                    <span class="status-unassigned">Belum Ditugaskan</span>
                                @endif
                            </td>
                            <td>{{ $sig['student_count'] }}</td>
                            <td>
                                <span class="grading-progress">{{ $sig['graded_count'] }} / {{ $sig['student_count'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('laporanSIG.view', $sig['id']) }}" class="btn-table-action" title="Lihat Laporan">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="garismasa-container">
            <div class="card-header">
                <h2>Garis Masa Kursus Inovasi Digital</h2>
            </div>
            <div class="card-body timelines">
                <div class="timeline-item">
                    <h3>Minggu 1:</h3>
                    <p>Pengenalan kepada Kursus Inovasi Digital</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 2-3:</h3>
                    <p>Brainstorming Idea dan Tujuan Program</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 4:</h3>
                    <p>Perancangan Program & Agihan Tugas</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 5:</h3>
                    <p>Pembentukan Jawatankuasa Program</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 6-7:</h3>
                    <p>Penyediaan Kertas Kerja & Kelulusan Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 8:</h3>
                    <p>Pembangunan Prototaip / Permulaan Pelaksanaan Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 9-10:</h3>
                    <p>Pelaksanaan Projek Lanjutan & Pengumpulan Data</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 11-12:</h3>
                    <p>Pengujian Sistem, Simulasi & Analisis Maklum Balas</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 13:</h3>
                    <p>Penambahbaikan & Kemasan Akhir Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 14:</h3>
                    <p>Penghantaran Tugasan (Laporan Akhir, Sistem & Bahan Pembentangan)</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 15:</h3>
                    <p>Pembentangan Projek & Penilaian Terakhir</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 16:</h3>
                    <p>Post-Mortem, Semakan Markah & Penutup Kursus</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endif

@if (Auth::user()->fld_user_role == 2)
<div class="dashboard-wrapper">

    <!-- Stats Row -->
    <div class="info-container">
        <div class="info-box box-pelajar">
            <div class="box-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="box-data">
                <h3>Jumlah Pelajar SIG</h3>
                <p class="big-number">{{ $totalPelajarSig }}</p>
            </div>
        </div>

        <div class="info-box box-kehadiran">
            <div class="box-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="box-data">
                <h3>Penilaian Selesai</h3>
                <p class="big-number">{{ $gradedCount }} / {{ $totalPelajarSig }}</p>
            </div>
        </div>

        <div class="info-box box-lewat">
            <div class="box-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="box-data">
                <h3>Tugasan Aktif</h3>
                <p class="big-number">{{ $tugasanAktif }}</p>
            </div>
        </div>
    </div>

    <!-- Ungraded Students Alert -->
    @if($ungradedStudents->count() > 0)
    <div class="admin-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{{ $ungradedStudents->count() }} pelajar belum dinilai.</strong>
            Sila pergi ke <a href="{{ route('penilaian') }}" style="color: #92400E; text-decoration: underline;">Penilaian Markah</a> untuk menilai mereka.
        </div>
    </div>
    @endif

    <div class="dashboard-content-row">
        <!-- Recent Assignments Table -->
        <div class="infoterkini-container">
            <div class="card-header">
                <h2>Senarai Tugasan Terkini</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama Tugasan</th>
                            <th>Tarikh Tutup</th>
                            <th>Penghantaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTugasan as $tgs)
                        <tr>
                            <td class="sig-name-cell">{{ $tgs->fld_tgs_nama }}</td>
                            <td>{{ \Carbon\Carbon::parse($tgs->fld_tgs_tarikh)->format('d M Y') }}</td>
                            <td>
                                <span class="grading-progress">{{ $tgs->penghantaran_count }} / {{ $totalPelajarSig }}</span>
                            </td>
                            <td>
                                @if($tgs->fld_tgs_status == 'Aktif')
                                    <span class="status-badge-active">Aktif</span>
                                @else
                                    <span class="status-badge-inactive">Tamat</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6B7280; padding: 24px;">Tiada tugasan direkodkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Garis Masa -->
        <div class="garismasa-container">
            <div class="card-header">
                <h2>Garis Masa Kursus Inovasi Digital</h2>
            </div>
            <div class="card-body timelines">
                <div class="timeline-item">
                    <h3>Minggu 1:</h3>
                    <p>Pengenalan kepada Kursus Inovasi Digital</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 2-3:</h3>
                    <p>Brainstorming Idea dan Tujuan Program</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 4:</h3>
                    <p>Perancangan Program & Agihan Tugas</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 5:</h3>
                    <p>Pembentukan Jawatankuasa Program</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 6-7:</h3>
                    <p>Penyediaan Kertas Kerja & Kelulusan Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 8:</h3>
                    <p>Pembangunan Prototaip / Permulaan Pelaksanaan Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 9-10:</h3>
                    <p>Pelaksanaan Projek Lanjutan & Pengumpulan Data</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 11-12:</h3>
                    <p>Pengujian Sistem, Simulasi & Analisis Maklum Balas</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 13:</h3>
                    <p>Penambahbaikan & Kemasan Akhir Projek</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 14:</h3>
                    <p>Penghantaran Tugasan (Laporan Akhir, Sistem & Bahan Pembentangan)</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 15:</h3>
                    <p>Pembentangan Projek & Penilaian Terakhir</p>
                </div>
                <div class="timeline-item">
                    <h3>Minggu 16:</h3>
                    <p>Post-Mortem, Semakan Markah & Penutup Kursus</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endif

@if (Auth::user()->fld_user_role == 3)
@php
    $pelajar = Auth::user()->pelajar;
    $sigId = $pelajar ? $pelajar->fld_sig_id : null;
    $nomat = $pelajar ? $pelajar->fld_pel_nomat : null;
    $attendance = $pelajar ? $pelajar->peratusan_kehadiran : 0;
    
    // Dapatkan senarai tugasan untuk SIG pelajar yang BELUM dihantar
    $tugasans = \App\Models\tugasan::where('fld_sig_id', $sigId)
                ->whereNotIn('fld_tgs_id', function($query) use ($nomat) {
                    $query->select('fld_tgs_id')
                          ->from('penghantaran')
                          ->where('fld_pel_nomat', $nomat);
                })
                ->orderBy('fld_tgs_tarikh', 'asc')
                ->get();
    
    $unhandedCount = $tugasans->count();
@endphp

<div class="dashboard-wrapper">
    
    <div class="dashboard-content-row">
        <!-- Left Side: Info Cards + Assignment List -->
        <div style="display: flex; flex-direction: column;">
            
            <!-- Top Info Cards -->
            <div class="info-container">
                <!-- Attendance -->
                <div class="info-box box-kehadiran">
                    <div class="box-icon">
                        <i class="fas fa-calendar-check"></i> 
                    </div>
                    <div class="box-data">
                        <h3>Peratusan Kehadiran</h3> 
                        <p class="big-number">{{ $attendance }}%</p> 
                    </div>
                </div>

                <!-- Assignments to hand in -->
                <div class="info-box box-lewat">
                    <div class="box-icon">
                        <i class="fas fa-tasks"></i> 
                    </div>
                    <div class="box-data">
                        <h3>Tugasan Perlu Dihantar</h3> 
                        <p class="big-number">{{ $unhandedCount }}</p> 
                    </div>
                </div>
            </div>

            <!-- Quick Links / Unhanded Assignments -->
            <div class="infoterkini-container">
                <div class="card-header">
                    <h2>Maklumat Terkini</h2> 
                </div>
                <div class="card-body">
                    <div class="assignment-list">
                        @forelse ($tugasans as $tugasan)
                            <a href="{{ route('tugasanPelajar') }}" class="assignment-item">
                                <div class="assignment-info">
                                    @if(str_contains(strtolower($tugasan->fld_tgs_jenis), 'video'))
                                        <i class="fas fa-video"></i>
                                    @elseif(str_contains(strtolower($tugasan->fld_tgs_jenis), 'laporan'))
                                        <i class="fas fa-file-alt"></i>
                                    @else
                                        <i class="fas fa-file-alt"></i>
                                    @endif
                                    <div class="assignment-details">
                                        <h4>{{ $tugasan->fld_tgs_nama }}</h4>
                                        <span class="due-date {{ \Carbon\Carbon::parse($tugasan->fld_tgs_tarikh)->isPast() ? 'text-danger' : '' }}">
                                            <i class="far fa-calendar-alt"></i> Due: {{ \Carbon\Carbon::parse($tugasan->fld_tgs_tarikh)->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right arrow-icon"></i>
                            </a>
                        @empty
                            <div class="empty-state">
                                <p>Tiada maklumat terkini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side: Garis Masa -->
        <div style="display: flex; flex-direction: column;">
            <div class="garismasa-container" style="height: 100%;">
                <div class="card-header">
                    <h2>Garis Masa Kursus Inovasi Digital</h2> 
                </div>
                <div class="card-body timelines">
                    <div class="timeline-item">
                        <h3>Minggu 1:</h3>
                        <p>Pengenalan kepada Kursus Inovasi Digital</p> 
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 2-3:</h3>
                        <p>Brainstorming Idea dan Tujuan Program</p> 
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 4:</h3>
                        <p>Perancangan Program & Agihan Tugas</p> 
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 5:</h3>
                        <p>Pembentukan Jawatankuasa Program</p> 
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 6-7:</h3>
                        <p>Penyediaan Kertas Kerja & Kelulusan Projek</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 8:</h3>
                        <p>Pembangunan Prototaip / Permulaan Pelaksanaan Projek</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 9-10:</h3>
                        <p>Pelaksanaan Projek Lanjutan & Pengumpulan Data</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 11-12:</h3>
                        <p>Pengujian Sistem, Simulasi & Analisis Maklum Balas</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 13:</h3>
                        <p>Penambahbaikan & Kemasan Akhir Projek</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 14:</h3>
                        <p>Penghantaran Tugasan (Laporan Akhir, Sistem & Bahan Pembentangan)</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 15:</h3>
                        <p>Pembentangan Projek & Penilaian Terakhir</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Minggu 16:</h3>
                        <p>Post-Mortem, Semakan Markah & Penutup Kursus</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endif

@endsection