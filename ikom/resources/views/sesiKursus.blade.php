@extends('layouts.appikom')
@section('title', '- Pendaftaran Kursus')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylesesiKursus.css') }}">

<div class="page-header">
    @if($sesiAktif)
        <div class="active-badge">
            <i class="fas fa-circle pulse-dot"></i>
            <span>Sesi Aktif: <strong>{{ $sesiAktif->fld_krs_nama }}</strong> – {{ $sesiAktif->fld_krs_semester }} {{ $sesiAktif->fld_krs_tahun }}</span>
        </div>
    @else
        <div class="active-badge badge-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Tiada sesi aktif. Sila aktifkan sesi.</span>
        </div>
    @endif
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert-flash alert-flash-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-flash alert-flash-error">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="two-col-layout">

    {{-- LEFT: Create New Session Form --}}
    <div class="card form-card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i>
            <h2>Cipta Sesi Baharu</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('sesiKursus.store') }}" method="POST" id="form-cipta-sesi">
                @csrf
                <div class="form-group">
                    <label for="nama_kursus">Nama Kursus</label>
                    <select name="nama_kursus" id="nama_kursus" class="form-control" required>
                        <option value="" disabled selected>-- Pilih Kursus --</option>
                        <option value="Inovasi Digital" {{ old('nama_kursus') == 'Inovasi Digital' ? 'selected' : '' }}>Inovasi Digital</option>
                        <option value="Komuniti Digital" {{ old('nama_kursus') == 'Komuniti Digital' ? 'selected' : '' }}>Komuniti Digital</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select name="semester" id="semester" class="form-control" required>
                        <option value="" disabled selected>-- Pilih Semester --</option>
                        <option value="Semester 1" {{ old('semester') == 'Semester 1' ? 'selected' : '' }}>Semester 1</option>
                        <option value="Semester 2" {{ old('semester') == 'Semester 2' ? 'selected' : '' }}>Semester 2</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahun">Tahun Akademik</label>
                    <select name="tahun" id="tahun" class="form-control" required>
                        <option value="" disabled selected>-- Pilih Tahun --</option>
                        @foreach(['2025/2026','2026/2027','2027/2028','2028/2029','2029/2030','2030/2031','2031/2032','2032/2033','2033/2034','2034/2035','2035/2036'] as $tahun)
                            <option value="{{ $tahun }}" {{ old('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary-full">
                    <i class="fas fa-save"></i> Simpan Sesi
                </button>
            </form>
        </div>
    </div>

    {{-- RIGHT: Session List --}}
    <div class="card list-card">
        <div class="card-header">
            <i class="fas fa-list-ul"></i>
            <h2>Semua Sesi Kursus</h2>
        </div>
        <div class="card-body no-padding">
            @if($sesiList->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Tiada sesi kursus dalam sistem lagi.</p>
                </div>
            @else
                <table class="sesi-table">
                    <thead>
                        <tr>
                            <th>Kursus</th>
                            <th>Semester</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sesiList as $sesi)
                        <tr class="{{ $sesi->fld_krs_aktif ? 'row-active' : '' }}">
                            <td>
                                <span class="course-pill {{ $sesi->fld_krs_nama == 'Inovasi Digital' ? 'pill-inovasi' : 'pill-komuniti' }}">
                                    {{ $sesi->fld_krs_nama }}
                                </span>
                            </td>
                            <td>{{ $sesi->fld_krs_semester }}</td>
                            <td>{{ $sesi->fld_krs_tahun }}</td>
                            <td>
                                @if($sesi->fld_krs_aktif)
                                    <span class="status-badge status-aktif">
                                        <i class="fas fa-circle"></i> Aktif
                                    </span>
                                @else
                                    <span class="status-badge status-tidak-aktif">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$sesi->fld_krs_aktif)
                                    <form action="{{ route('sesiKursus.aktif', $sesi->fld_krs_id) }}" method="POST"
                                          onsubmit="return confirm('Aktifkan sesi {{ $sesi->fld_krs_nama }} – {{ $sesi->fld_krs_semester }} {{ $sesi->fld_krs_tahun }}? Sesi lain akan dinyahaktifkan.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-activate">
                                            <i class="fas fa-power-off"></i> Aktifkan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted-sm">Sedang Aktif</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>
@endsection
