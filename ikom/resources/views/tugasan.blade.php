@extends('layouts.appikom')

@section('title', '- Tugasan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styleTugasan.css') }}?v={{ filemtime(public_path('css/styleTugasan.css')) }}">

@if (Auth::user()->fld_user_role == 2)
<div class="tugasan-wrapper">

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

    <!-- Header Actions -->
    <div id="tugasanHeader" class="list-header">
        <button class="btn-submit btn-submit-auto" onclick="showCreateForm()">
            <i class="fas fa-plus"></i> Tambah Tugasan
        </button>
    </div>

    <!-- Data List -->
    <div class="glass-effect table-container" id="tugasanList">
        @if($tugasans->count() > 0)
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tajuk</th>
                    <th>Tarikh Tutup</th>
                    <th>Status</th>
                    <th style="width: 120px;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tugasans as $tgs)
                <tr>
                    <td>
                        <a href="javascript:void(0)" onclick="showDetails('{{ addslashes($tgs->fld_tgs_nama) }}', '{{ addslashes($tgs->fld_tgs_desc) }}', '{{ \Carbon\Carbon::parse($tgs->fld_tgs_tarikh)->format('d M Y') }}', '{{ $tgs->penghantaran_count }}', '{{ $tgs->fld_tgs_file ? asset('lampiran_tugasan/'.$tgs->fld_tgs_file) : '' }}', '{{ $tgs->fld_tgs_status }}')" class="title-link">
                            <strong>{{ $tgs->fld_tgs_nama }}</strong>
                        </a>
                    </td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($tgs->fld_tgs_tarikh)->format('d M Y') }}</td>
                    <td>
                        <span class="status-badge {{ $tgs->fld_tgs_status == 'Aktif' ? 'status-aktif' : 'status-tidak-aktif' }}">
                            {{ $tgs->fld_tgs_status }}
                        </span>
                    </td>
                    <td>
                        <!-- Edit Button -->
                        <button type="button" title="Kemaskini" onclick="showEditForm({{ $tgs->fld_tgs_id }}, '{{ addslashes($tgs->fld_tgs_nama) }}', '{{ addslashes($tgs->fld_tgs_desc) }}', '{{ $tgs->fld_tgs_tarikh }}')" class="btn-action-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <!-- Delete Button -->
                        <form action="{{ route('tugasan.delete', $tgs->fld_tgs_id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Adakah anda pasti untuk memadam tugasan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Padam" class="btn-action-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <i class="fas fa-folder-open fa-3x"></i>
            <p>Tiada tugasan yang direkodkan setakat ini.</p>
        </div>
        @endif
    </div>

    <!-- Hidden Form (Create/Edit) -->
    <div class="glass-effect section-container" id="tugasanFormSection">
        <button type="button" onclick="hideForm()" class="btn-close-section">
            <i class="fas fa-times"></i>
        </button>
        
        <h2 id="formTitle" class="section-title"><i class="fas fa-file-alt"></i> Tambah Tugasan</h2>

        <form id="tugasanForm" action="{{ route('tugasan.store') }}" method="POST" enctype="multipart/form-data" class="tugasan-form">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="form-group">
                <label for="tugasan_title"><i class="fas fa-heading"></i> Tajuk Tugasan</label>
                <input type="text" id="tugasan_title" name="tugasan_title" class="form-control" placeholder="Cth: Pembangunan Prototaip Web" required>
            </div>

            <div class="form-group">
                <label for="tugasan_desc"><i class="fas fa-align-left"></i> Penerangan Tugasan</label>
                <textarea id="tugasan_desc" name="tugasan_desc" class="form-control" rows="5" placeholder="Sila berikan penerangan terperinci tentang kehendak tugasan ini..." required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group half-width">
                    <label for="due_date"><i class="fas fa-calendar-alt"></i> Tarikh Tutup</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required>
                </div>
                
                <div class="form-group half-width">
                    <label for="tugasan_file"><i class="fas fa-paperclip"></i> Lampiran (Pilihan)</label>
                    <input type="file" id="tugasan_file" name="tugasan_file" class="form-control file-input">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtnText">
                    <i class="fas fa-paper-plane"></i> Hantar Tugasan
                </button>
                <button type="button" class="btn-reset" onclick="hideForm()">
                    <i class="fas fa-times-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Hidden Details Section -->
    <div class="glass-effect section-container" id="tugasanDetailsSection">
        <button type="button" onclick="hideDetails()" class="btn-close-section">
            <i class="fas fa-times"></i>
        </button>
        
        <h2 id="detailTitle" class="section-title"><i class="fas fa-tasks"></i> Maklumat Tugasan</h2>

        <div class="detail-panel">
            <h3>Penerangan</h3>
            <p id="detailDesc"></p>
        </div>

        <div class="detail-panel-row">
            <div class="detail-panel-small">
                <span class="detail-label"><i class="fas fa-calendar-alt"></i> Tarikh Tutup</span>
                <strong id="detailDate" class="detail-value"></strong>
            </div>
            <div class="detail-panel-small">
                <span class="detail-label"><i class="fas fa-users"></i> Jumlah Pelajar Menghantar</span>
                <strong id="detailCount" class="detail-value-large">0</strong>
            </div>
            <div class="detail-panel-small">
                <span class="detail-label"><i class="fas fa-info-circle"></i> Status</span>
                <strong id="detailStatus"></strong>
            </div>
        </div>

        <!-- Action Button row -->
        <div class="detail-panel-row" style="margin-top: 15px; margin-bottom: 0;">
            <div class="detail-panel-small" style="background: transparent; border: none; padding: 0; box-shadow: none;">
                <a id="btnSemakTugasan" href="{{ url('/semakanTugasan') }}" class="btn-submit" style="display: inline-flex; justify-content: center; width: 100%; font-size: 1.1rem; padding: 15px; text-decoration: none;">
                    <i class="fas fa-check-double"></i> Semak Tugasan Pelajar
                </a>
            </div>
        </div>

        <div id="detailFileWrapper" class="detail-panel-small" style="display:none; flex:unset;">
            <span class="detail-label"><i class="fas fa-paperclip"></i> Lampiran Fail</span>
            <a id="detailFileLink" href="#" target="_blank" class="file-download-link">
                <i class="fas fa-download"></i> Muat Turun Lampiran
            </a>
        </div>
    </div>

</div>

<script>
    // Variables holding the original POST route
    const storeRoute = "{{ route('tugasan.store') }}";
    
    function showCreateForm() {
        document.getElementById('tugasanList').style.display = 'none';
        document.getElementById('tugasanHeader').style.display = 'none';
        document.getElementById('tugasanDetailsSection').style.display = 'none';
        document.getElementById('tugasanFormSection').style.display = 'block';
        
        // Reset form to default Add mode
        document.getElementById('tugasanForm').reset();
        document.getElementById('tugasanForm').action = storeRoute;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-file-alt"></i> Tambah Tugasan';
        document.getElementById('submitBtnText').innerHTML = '<i class="fas fa-paper-plane"></i> Hantar Tugasan';
    }

    function showEditForm(id, title, desc, date) {
        document.getElementById('tugasanList').style.display = 'none';
        document.getElementById('tugasanHeader').style.display = 'none';
        document.getElementById('tugasanDetailsSection').style.display = 'none';
        document.getElementById('tugasanFormSection').style.display = 'block';
        
        // Populate inputs
        document.getElementById('tugasan_title').value = title;
        document.getElementById('tugasan_desc').value = desc;
        document.getElementById('due_date').value = date;
        
        // Set update mode parameters
        document.getElementById('tugasanForm').action = "/tugasan/" + id;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit"></i> Kemaskini Tugasan';
        document.getElementById('submitBtnText').innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
    }

    function showDetails(title, desc, date, count, path, status) {
        document.getElementById('tugasanList').style.display = 'none';
        document.getElementById('tugasanHeader').style.display = 'none';
        document.getElementById('tugasanFormSection').style.display = 'none';
        document.getElementById('tugasanDetailsSection').style.display = 'block';

        document.getElementById('detailTitle').innerHTML = '<i class="fas fa-tasks"></i> ' + title;
        document.getElementById('detailDesc').textContent = desc;
        document.getElementById('detailDate').textContent = date;
        document.getElementById('detailCount').textContent = count;
        
        if (status === 'Aktif') {
            document.getElementById('detailStatus').innerHTML = '<span style="color:#166534; background:#dcfce7; padding:4px 8px; border-radius:8px;">Aktif</span>';
        } else {
            document.getElementById('detailStatus').innerHTML = '<span style="color:#991b1b; background:#fee2e2; padding:4px 8px; border-radius:8px;">' + status + '</span>';
        }

        if (path) {
            document.getElementById('detailFileWrapper').style.display = 'block';
            document.getElementById('detailFileLink').href = path;
        } else {
            document.getElementById('detailFileWrapper').style.display = 'none';
        }
    }

    function hideForm() {
        document.getElementById('tugasanFormSection').style.display = 'none';
        document.getElementById('tugasanList').style.display = 'block';
        document.getElementById('tugasanHeader').style.display = 'flex';
    }

    function hideDetails() {
        document.getElementById('tugasanDetailsSection').style.display = 'none';
        document.getElementById('tugasanList').style.display = 'block';
        document.getElementById('tugasanHeader').style.display = 'flex';
    }
</script>
@endif

@endsection
