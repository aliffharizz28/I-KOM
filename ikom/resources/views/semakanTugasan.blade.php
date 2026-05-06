@extends('layouts.appikom')

@section('title', '- Semakan Tugasan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styleSemakanTugasan.css') }}?v={{ time() }}">

<div class="semakan-wrapper">

    <div class="semakan-container">
        <!-- Letak Kiri: Preview Fail -->
        <div class="preview-side">
            <div class="glass-panel preview-panel">
                <div class="preview-header">
                    <h3><i class="fas fa-file-pdf"></i> Semakan Penghantaran</h3>
                    <div class="preview-header-actions">
                        <button id="btnBackToList" onclick="backToTable()" class="btn-back-to-list" style="display:none;"><i class="fas fa-arrow-left"></i> Kembali ke Senarai</button>
                        <span id="currentStudentName" class="active-student-badge">Senarai Penghantaran</span>
                    </div>
                </div>
                <div class="preview-content" id="previewContent">
                    <!-- Table for submissions -->
                    <div id="submissionTableContainer" class="submission-table-container">
                        <table class="custom-table submission-table">
                            <thead>
                                <tr>
                                    <th>Nama Pelajar</th>
                                    <th>Tarikh Hantar</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penghantarans as $penghantaran)
                                <tr>
                                    <td>{{ $penghantaran->pelajar->pengguna->fld_user_nama ?? 'Tiada Nama' }}</td>
                                    <td>{{ $penghantaran->created_at ? \Carbon\Carbon::parse($penghantaran->created_at)->format('d M Y h:i A') : 'Tiada Tarikh' }}</td>
                                    <td class="text-center">
                                        <button class="btn-submit btn-view-submission" onclick="viewSubmission('{{ addslashes($penghantaran->pelajar->pengguna->fld_user_nama ?? 'Pelajar') }}', '{{ asset('lampiran_penghantaran/'.$penghantaran->fld_pgh_fail) }}')"><i class="fas fa-eye"></i> Semak Fail</button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="empty-submission-row">
                                    <td colspan="3">Belum ada penghantaran daripada pelajar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Iframe for actual preview will be injected here -->
                    <iframe id="fileViewer" src="" class="file-viewer-iframe" style="display:none;"></iframe>
                </div>
            </div>
        </div>

        <!-- Letak Kanan: Senarai Pelajar & Markah -->
        <div class="list-side">
            <div class="glass-panel marks-panel">
                <form action="{{ route('semakanTugasan.saveMarks', $tugasan->fld_tgs_id) }}" method="POST">
                    @csrf
                    <div class="list-header">
                        <h3><i class="fas fa-highlighter"></i> Pemarkahan Pelajar</h3>
                        <div class="search-box">
                            <input type="text" placeholder="Cari nama pelajar..." class="search-input">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    
                    @if(session('success'))
                        <div class="success-alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="student-list student-list-container">
                        @foreach($pelajars as $pelajar)
                        @php
                            $nama = $pelajar->pengguna->fld_user_nama ?? 'Tiada Nama';
                            $initials = collect(explode(' ', $nama))->map(function($word) { return strtoupper(substr($word, 0, 1)); })->take(2)->join('');
                            $hasSubmission = isset($penghantarans[$pelajar->fld_pel_nomat]);
                            $mark = $hasSubmission ? $penghantarans[$pelajar->fld_pel_nomat]->fld_pgh_markah : '';
                        @endphp
                        <div class="student-card student-card-default {{ !$hasSubmission ? 'student-card-no-submission' : '' }}">
                            <div class="student-info student-info-center">
                                @if($pelajar->fld_pel_pic)
                                    <img src="{{ asset('storage/' . $pelajar->fld_pel_pic) }}" alt="Avatar" class="student-pic">
                                @else
                                    <div class="student-avatar student-avatar-margin">{{ $initials }}</div>
                                @endif
                                <div class="student-details">
                                    <h4 class="student-name-bold">{{ $nama }}</h4>
                                    @if(!$hasSubmission)
                                        <small class="student-no-submission-text">Belum Hantar</small>
                                    @endif
                                </div>
                            </div>
                            <div class="mark-input-group">
                                <input type="number" name="marks[{{ $pelajar->fld_pel_nomat }}]" class="mark-input" value="{{ $mark }}" placeholder="-" min="0" max="10" step="1" {{ !$hasSubmission ? 'disabled' : '' }}>
                                <span class="mark-divider">/ 10</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="panel-footer">
                        <button type="submit" class="btn-save-marks"><i class="fas fa-save"></i> Simpan Semua Markah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function viewSubmission(studentName, fileUrl) {
        // Update Header
        document.getElementById('currentStudentName').textContent = studentName;
        document.getElementById('btnBackToList').style.display = 'inline-block';
        
        // Render File Table to none, open File Viewer
        document.getElementById('submissionTableContainer').style.display = 'none';
        const viewer = document.getElementById('fileViewer');
        viewer.style.display = 'block';
        
        viewer.src = fileUrl;
    }

    function backToTable() {
        document.getElementById('currentStudentName').textContent = 'Senarai Penghantaran';
        document.getElementById('btnBackToList').style.display = 'none';
        
        document.getElementById('fileViewer').src = '';
        document.getElementById('fileViewer').style.display = 'none';
        document.getElementById('submissionTableContainer').style.display = 'block';
    }
</script>
@endsection
