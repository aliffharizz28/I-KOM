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
                                @forelse($penghantarans->filter(function($p) { return !empty($p->fld_pgh_fail); }) as $penghantaran)
                                <tr>
                                    <td>{{ $penghantaran->pelajar->pengguna->fld_user_nama ?? 'Tiada Nama' }}</td>
                                    <td>{{ $penghantaran->created_at ? \Carbon\Carbon::parse($penghantaran->created_at)->format('d M Y h:i A') : 'Tiada Tarikh' }}</td>
                                    <td class="text-center">
                                        <button class="btn-submit btn-view-submission" onclick="viewSubmission('{{ addslashes($penghantaran->pelajar->pengguna->fld_user_nama ?? 'Pelajar') }}', '{{ route('file.penghantaran', $penghantaran->fld_pgh_fail) }}')"><i class="fas fa-eye"></i> Semak Fail</button>
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
                <form class="marks-form" action="{{ route('semakanTugasan.saveMarks', $tugasan->fld_tgs_id) }}" method="POST">
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

                    @if(session('error'))
                        <div class="error-alert" style="background-color: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; padding: 12px 16px; border-radius: 6px; margin: 16px 24px 0 24px; font-size: 0.95rem; font-weight: 500;">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="student-list student-list-container">
                        @foreach($pelajars as $pelajar)
                        <div class="student-card student-card-default {{ !$pelajar->has_submission ? 'student-card-no-submission' : '' }}">
                            <div class="student-info student-info-center">
                                @if($pelajar->has_pic)
                                    <img src="{{ $pelajar->final_pic_url }}" alt="Avatar" class="student-pic">
                                @else
                                    <div class="student-avatar student-avatar-margin">{{ $pelajar->initials }}</div>
                                @endif
                                <div class="student-details">
                                    <h4 class="student-name-bold">{{ $pelajar->display_nama }}</h4>
                                    @if(!$pelajar->has_submission)
                                        <small class="student-no-submission-text">Belum Hantar</small>
                                    @endif
                                </div>
                            </div>
                            <div class="mark-input-group">
                                <input type="number" name="marks[{{ trim($pelajar->fld_pel_nomat) }}]" class="mark-input" value="{{ $pelajar->mark }}" placeholder="-" min="0" max="10" step="1">
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
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Carian Pelajar
        const searchInput = document.querySelector('.search-input');
        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const studentCards = document.querySelectorAll('.student-card');
                
                studentCards.forEach(card => {
                    const studentNameElement = card.querySelector('.student-name-bold');
                    if (studentNameElement) {
                        const studentName = studentNameElement.textContent.toLowerCase();
                        if(studentName.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        }

        // 2. Elak simpan markah bila tekan "Enter"
        const marksForm = document.querySelector('.marks-form');
        if(marksForm) {
            marksForm.addEventListener('keydown', function(e) {
                // Prevent submission if enter is pressed on inputs (not on submit button or textarea)
                if(e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            });
        }
    });

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
