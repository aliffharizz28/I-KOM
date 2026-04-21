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
                    <h3><i class="fas fa-file-pdf"></i> Pratonton Fail</h3>
                    <span id="currentStudentName" class="active-student-badge">Sila pilih pelajar</span>
                </div>
                <div class="preview-content" id="previewContent">
                    <div class="empty-preview" id="emptyPreview">
                        <i class="fas fa-file-invoice fa-4x"></i>
                        <p>Pilih pelajar dari senarai di sebelah kanan untuk melihat fail tugasan.</p>
                    </div>
                    <!-- Iframe for actual preview will be injected here -->
                    <iframe id="fileViewer" src="" style="display:none; width: 100%; height: 100%; border: none; border-radius: 8px;"></iframe>
                </div>
            </div>
        </div>

        <!-- Letak Kanan: Senarai Pelajar & Markah -->
        <div class="list-side">
            <div class="glass-panel marks-panel">
                <div class="list-header">
                    <h3><i class="fas fa-users"></i> Senarai Pelajar</h3>
                    <div class="search-box">
                        <input type="text" placeholder="Cari nama pelajar..." class="search-input">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="student-list">
                    <!-- Placeholder UI Data -->
                    
                    <!-- Pelajar 1 -->
                    <div class="student-card" onclick="viewSubmission(this, 'Ali bin Abu', 'sample1.pdf', 8)">
                        <div class="student-info">
                            <div class="student-avatar">AL</div>
                            <div class="student-details">
                                <h4>Ali bin Abu</h4>
                                <span class="submit-time"><i class="fas fa-clock"></i> Dihantar: 12 Nov 2023</span>
                                <span class="status-badge status-marked">Telah Disemak</span>
                            </div>
                        </div>
                        <div class="mark-input-group" onclick="event.stopPropagation();">
                            <input type="number" class="mark-input" value="8" min="0" max="10" step="1">
                            <span class="mark-divider">/ 10</span>
                        </div>
                    </div>

                    <!-- Pelajar 2 (Belum disemak) -->
                    <div class="student-card" onclick="viewSubmission(this, 'Siti Aminah', 'sample2.pdf', '')">
                        <div class="student-info">
                            <div class="student-avatar">SA</div>
                            <div class="student-details">
                                <h4>Siti Aminah</h4>
                                <span class="submit-time"><i class="fas fa-clock"></i> Dihantar: 13 Nov 2023</span>
                                <span class="status-badge status-unmarked">Belum Disemak</span>
                            </div>
                        </div>
                        <div class="mark-input-group" onclick="event.stopPropagation();">
                            <input type="number" class="mark-input" value="" placeholder="-" min="0" max="10" step="1">
                            <span class="mark-divider">/ 10</span>
                        </div>
                    </div>
                    
                    <!-- Pelajar 3 -->
                    <div class="student-card" onclick="viewSubmission(this, 'Ahmad Albab', 'sample3.pdf', 5)">
                        <div class="student-info">
                            <div class="student-avatar">AA</div>
                            <div class="student-details">
                                <h4>Ahmad Albab</h4>
                                <span class="submit-time"><i class="fas fa-clock"></i> Dihantar: 14 Nov 2023</span>
                                <span class="status-badge status-marked">Telah Disemak</span>
                            </div>
                        </div>
                        <div class="mark-input-group" onclick="event.stopPropagation();">
                            <input type="number" class="mark-input" value="5" min="0" max="10" step="1">
                            <span class="mark-divider">/ 10</span>
                        </div>
                    </div>

                    <!-- Pelajar 4 -->
                    <div class="student-card" onclick="viewSubmission(this, 'Nurul Huda', 'sample4.pdf', 9)">
                        <div class="student-info">
                            <div class="student-avatar">NH</div>
                            <div class="student-details">
                                <h4>Nurul Huda</h4>
                                <span class="submit-time"><i class="fas fa-clock"></i> Dihantar: 15 Nov 2023</span>
                                <span class="status-badge status-marked">Telah Disemak</span>
                            </div>
                        </div>
                        <div class="mark-input-group" onclick="event.stopPropagation();">
                            <input type="number" class="mark-input" value="9" min="0" max="10" step="1">
                            <span class="mark-divider">/ 10</span>
                        </div>
                    </div>
                </div>
                
                <div class="panel-footer">
                    <button class="btn-save-marks"><i class="fas fa-save"></i> Simpan Semua Markah</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewSubmission(element, studentName, fileUrl, currentMark) {
        // Update active state
        document.querySelectorAll('.student-card').forEach(card => card.classList.remove('active-card'));
        element.classList.add('active-card');
        
        // Update Header
        document.getElementById('currentStudentName').textContent = studentName;
        
        // Show iframe and hide empty state
        document.getElementById('emptyPreview').style.display = 'none';
        const viewer = document.getElementById('fileViewer');
        viewer.style.display = 'block';
        
        // Mock file loading (Using data URI for placeholder PDF preview effect)
        viewer.src = "data:application/pdf;base64,JVBERi0xLjcKCjEgMCBvYmogICUKPDwKICAvVHlwZSAvQ2F0YWxvZwogIC9QYWdlcyAyIDAgUgo+PgplbmRvYmoKCjIgMCBvYmoKPDwKICAvVHlwZSAvUGFnZXMKICAvTWVkaWFCb3ggWyAwIDAgNjEyIDc5MiBdCiAgL0NvdW50IDEKICAvS2lkcyBbIDMgMCBSIF0KPj4KZW5kb2JqCgozIDAgb2JqCjw8CiAgL1R5cGUgL1BhZ2UKICAvUGFyZW50IDIgMCBSCiAgL1Jlc291cmNlcyA8PAogICAgL0ZvbnQgPDwKICAgICAgL0YxIDQgMCBSCgkvRm9udEItMSAzIDAgUgo+PgogID4+CiAgL0NvbnRlbnRzIDUgMCBSCj4+CmVuZG9iagoKNCAwIG9iago8PAogIC9UeXBlIC9Gb250CiAgL1N1YnR5cGUgL1R5cGUxCiAgL0Jhc2VGb250IC9IZWx2ZXRpY2EtdW5peG9zCj4+CmVuZG9iagoKNSAwIG9iago8PCAvTGVuZ3RoIDY5ICBQPgpzdHJlYW0KQlQKL0YxIDI0IFRmCjIwMCA0MDAgVGQKKFRoaXMgaXMgYSBwbGFjZWhvbGRlciBmaWxlIHByZXZpZXcuKSBUagpFVAplbmRzdHJlYW0KZW5kb2JqCgp4cmVmCjAgNgowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDAwMDkgMDAwMDAgbiAKMDAwMDAwMDA1NiAwMDAwMCBuIAowMDAwMDAwMTQ2IDAwMDAwIG4gCjAwMDAwMDAyNDEgMDAwMDAgbiAKMDAwMDAwMDMzNiAwMDAwMCBuIAp0cmFpbGVyCjw8CiAgL1NpemUgNgogIC9Sb290IDEgMCBSCj4+CnN0YXJ0eHJlZgo0NTYKJSVFT0YK";
    }
</script>
@endsection
