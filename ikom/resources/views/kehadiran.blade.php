@extends('layouts.appikom')

@section('title', '- Kehadiran')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylekehadiran.css') }}?v={{ file_exists(public_path('css/stylekehadiran.css')) ? filemtime(public_path('css/stylekehadiran.css')) : '' }}">

<div class="kehadiran-wrapper">
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(Auth::user()->fld_user_role == 2)
        <!-- Role 2: Penyelaras SIG -->
        <div class="list-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2><i class="fas fa-check-double"></i> Pengesahan Kehadiran Perjumpaan SIG</h2>
            <a href="{{ route('kehadiran.export') }}" class="btn-submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 8px 16px;">
                <i class="fas fa-file-excel"></i> Muat Turun Laporan
            </a>
        </div>

        <div class="glass-effect table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Topik Perjumpaan</th>
                        <th>Tarikh</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perjumpaans as $p)
                        <tr>
                            <td>{{ $p->fld_meet_topik }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->fld_meet_tarikh)->format('d M Y') }}</td>
                            <td>
                                {!! $p->fld_meet_verify ? '<span class="status-badge" style="background:#c6f6d5;color:#2f855a;">Disahkan</span>' : '<span class="status-badge" style="background:#fed7d7;color:#c53030;">Belum Disahkan</span>' !!}
                            </td>
                            <td>
                                <a href="{{ route('kehadiran.rekod', $p->fld_meet_id) }}" class="btn-submit" style="padding: 5px 10px; font-size: 14px;">
                                    <i class="fas fa-eye"></i> Semak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tiada data untuk dipaparkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif(Auth::user()->fld_user_role == 3 && Auth::user()->pelajar && Auth::user()->pelajar->fld_pel_mt == 1)
        <!-- Role 3: Pelajar (MT) -->
        <div class="list-header" id="mtHeader">
            <h2><i class="fas fa-clipboard-list"></i> Rekod Kehadiran Perjumpaan SIG</h2>
            <button class="btn-submit" onclick="showCreateForm()">
                <i class="fas fa-plus"></i> Tambah Perjumpaan
            </button>
        </div>

        <div class="glass-effect table-container" id="perjumpaanList">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Topik Perjumpaan</th>
                        <th>Tarikh</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perjumpaans as $p)
                        <tr>
                            <td>{{ $p->fld_meet_topik }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->fld_meet_tarikh)->format('d M Y') }}</td>
                            <td>
                                {!! $p->fld_meet_verify ? '<span class="status-badge" style="background:#c6f6d5;color:#2f855a;">Disahkan</span>' : '<span class="status-badge" style="background:#fed7d7;color:#c53030;">Belum Disahkan</span>' !!}
                            </td>
                            <td>
                                <a href="{{ route('kehadiran.rekod', $p->fld_meet_id) }}" class="btn-submit" style="padding: 5px 10px; font-size: 14px;">
                                    <i class="fas fa-clipboard-check"></i> Rekod Kehadiran
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tiada data untuk dipaparkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Hidden Form -->
        <div class="glass-effect section-container" id="perjumpaanFormSection" style="display: none;">
            <button type="button" onclick="hideForm()" class="btn-close-section">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="section-title"><i class="fas fa-calendar-plus"></i> Tambah Perjumpaan Baru</h2>
            
            <form id="perjumpaanForm" action="{{ route('kehadiran.storePerjumpaan') }}" method="POST" class="kehadiran-form">
                @csrf
                <div class="form-group">
                    <label for="topik"><i class="fas fa-heading"></i> Topik Perjumpaan</label>
                    <input type="text" id="topik" name="topik" class="form-control" required placeholder="Cth: Perjumpaan Minggu 1">
                </div>
                <div class="form-group">
                    <label for="tarikh"><i class="fas fa-calendar-alt"></i> Tarikh</label>
                    <input type="date" id="tarikh" name="tarikh" class="form-control" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Cipta & Rekod Kehadiran</button>
                    <button type="button" class="btn-reset" onclick="hideForm()"><i class="fas fa-times-circle"></i> Batal</button>
                </div>
            </form>
        </div>
    @endif
</div>

<script>
    function showCreateForm() {
        document.getElementById('perjumpaanList').style.display = 'none';
        document.getElementById('mtHeader').style.display = 'none';
        document.getElementById('perjumpaanFormSection').style.display = 'block';
    }

    function hideForm() {
        document.getElementById('perjumpaanFormSection').style.display = 'none';
        document.getElementById('perjumpaanList').style.display = 'block';
        document.getElementById('mtHeader').style.display = 'flex';
    }
</script>
@endsection
