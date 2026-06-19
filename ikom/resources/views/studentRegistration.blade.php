@extends('layouts.appikom')

@section('title', '- Pendaftaran Pelajar')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylestudentregister.css') }}?v={{ filemtime(public_path('css/stylestudentregister.css')) }}">

<div class="registration-container">
    <div id="alert-box" class="alert"></div>

    <div class="toggle-container">
        <button type="button" class="toggle-btn active" onclick="switchTab('bulk')">Import Pelajar</button>
        <button type="button" class="toggle-btn" onclick="switchTab('individual')">Pendaftaran Individu</button>
    </div>

    <!-- Bulk Registration Section --> 
    <div id="bulk-section" class="section active">
        <h3>Import Pelajar</h3>
        <p>Muat naik fail .csv yang mengandungi senarai nombor matrik pelajar untuk mendaftarkan mereka ke SIG.</p>
        
        <div class="drag-drop-area" id="drag-drop-area" onclick="document.getElementById('csv_file').click()">
            <p>Seret dan lepas fail .csv anda di sini atau klik untuk layari</p>
            <input type="file" id="csv_file" accept=".csv" onchange="handleFileSelect(event)" />
        </div>
        <div id="file-name-display" class="file-name-display"></div>
        
        <button type="button" class="btn-primary mt-15" onclick="registerBulk()">Muat Naik & Daftar</button>

    </div>

    <!-- Individual Registration Section -->
    <div id="individual-section" class="section">
        <h3>Pendaftaran Individu</h3>
        <div class="form-group">
            <label>Nombor Matrik</label>
            <div class="search-flex-container">
                <input type="text" id="matric_search" placeholder="Contoh : A203272" class="flex-1" />
                <button type="button" class="btn-primary btn-search" onclick="fetchStudent()">Cari Pelajar</button>
            </div>
        </div>

        <div id="student-form-container" style="display: none;">
            <div class="student-card">
                <div class="student-picture-container">
                    <img id="student_pic" src="{{ asset('pic/picpel1.jpg') }}" alt="Gambar Profil"/>
                </div>
                <div class="student-details">
                    <div class="detail-item">
                        <span class="detail-label">Nama</span>
                        <strong id="student_name" class="detail-value" style="font-size: 1.1rem;"></strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jurusan</span>
                        <span id="student_program" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">No. Matrik</span>
                        <span id="student_matric" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Emel</span>
                        <span id="student_email" class="detail-value"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-primary mt-15" onclick="registerIndividual()">Daftar ke SIG</button>
        </div>

    </div>

    <div class="table-section-container">
        <h3>Senarai Pelajar Berdaftar</h3>
        <table id="registered-students-table" class="students-table">
            <thead>
                <tr>
                    <th>Bil.</th>
                    <th>Nombor Matrik</th>
                    <th>Nama</th>
                    <th>Jurusan</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($registeredStudents) && count($registeredStudents) > 0)
                    @foreach($registeredStudents as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->fld_pel_nomat }}</td>
                            <td>{{ $student->pengguna ? $student->pengguna->fld_user_nama : 'Tiada Nama' }}</td>
                            <td>{{ $student->fld_pel_jurusan }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr id="no-data-row">
                        <td colspan="4" class="text-center">Tiada pelajar berdaftar lagi.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div id="pagination-controls" class="pagination-footer"></div>
    </div>
</div>

<script>        
    const csrfToken = '{{ csrf_token() }}';

    function showAlert(message, isError = false) {
        const alertBox = document.getElementById('alert-box');
        alertBox.textContent = message;
        alertBox.className = 'alert ' + (isError ? 'alert-error' : 'alert-success');
        alertBox.style.display = 'block';
    }

    function switchTab(tab) {
        document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
        
        if (tab === 'individual') {
            document.querySelectorAll('.toggle-btn')[1].classList.add('active');
            document.getElementById('individual-section').classList.add('active');
        } else {
            document.querySelectorAll('.toggle-btn')[0].classList.add('active');
            document.getElementById('bulk-section').classList.add('active');
        }
    }

    let currentPage = 1;
    const rowsPerPage = 10;

    function updateTableIndices() {
        const rows = document.querySelectorAll('#registered-students-table tbody tr:not(#no-data-row)');
        rows.forEach((row, index) => {
            const firstCell = row.querySelector('td:first-child');
            if (firstCell) {
                firstCell.textContent = index + 1;
            }
        });
    }

    function renderTablePagination() {
        const rows = document.querySelectorAll('#registered-students-table tbody tr:not(#no-data-row)');
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const paginationControls = document.getElementById('pagination-controls');

        if (totalRows === 0) {
            if (paginationControls) paginationControls.style.display = 'none';
            return;
        }
        
        if (paginationControls) paginationControls.style.display = 'flex';
        
        // Ensure currentPage is within range
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Show/Hide rows based on currentPage
        rows.forEach((row, index) => {
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Render pagination footer content
        const startItem = (currentPage - 1) * rowsPerPage + 1;
        const endItem = Math.min(currentPage * rowsPerPage, totalRows);
        
        let html = `
            <div class="pagination-info">
                Menunjukkan <strong>${startItem}</strong> hingga <strong>${endItem}</strong> daripada <strong>${totalRows}</strong> rekod
            </div>
            <div class="pagination-buttons">
                <button type="button" class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">
                    &laquo;
                </button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (totalPages <= 7 || i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `
                    <button type="button" class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                        ${i}
                    </button>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="pagination-btn" style="border: none; background: transparent; cursor: default; pointer-events: none;">...</span>`;
            }
        }

        html += `
                <button type="button" class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
                    &raquo;
                </button>
            </div>
        `;

        if (paginationControls) paginationControls.innerHTML = html;
    }

    function goToPage(page) {
        currentPage = page;
        renderTablePagination();
    }

    // --- Individual Registration Logic ---
    function fetchStudent() {
        const matric = document.getElementById('matric_search').value.trim();
        if (!matric) {
            showAlert('Sila masukkan nombor matrik.', true);
            return;
        }

        fetch(`/registration/fetch-student`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ matric_number: matric })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('student-form-container').style.display = 'block';
                document.getElementById('student_pic').src = data.data.pic;
                document.getElementById('student_name').textContent = data.data.name;
                document.getElementById('student_matric').textContent = data.data.matric_number;
                document.getElementById('student_program').textContent = data.data.program;
                document.getElementById('student_email').textContent = data.data.email;
            } else {
                showAlert(data.message, true);
                document.getElementById('student-form-container').style.display = 'none';
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('Ralat mendapatkan maklumat pelajar.', true);
        });
    }

    function registerIndividual() {
        const matric = document.getElementById('student_matric').textContent;

        fetch(`/registration/individual`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ matric_number: matric })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message);
                
                const noDataRow = document.getElementById('no-data-row');
                if (noDataRow) noDataRow.remove();
                
                const tbody = document.querySelector('#registered-students-table tbody');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td></td>
                    <td>${data.data.matric_number}</td>
                    <td>${data.data.name}</td>
                    <td>${data.data.program || ''}</td>
                `;
                tbody.prepend(tr);
                updateTableIndices();
                currentPage = 1;
                renderTablePagination();

                // Reset form
                document.getElementById('student-form-container').style.display = 'none';
                document.getElementById('matric_search').value = '';
            } else {
                showAlert(data.message, true);
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('Ralat mendaftar pelajar.', true);
        });
    }

    // --- Bulk Registration Logic ---
    const dropArea = document.getElementById('drag-drop-area');
    let selectedFile = null;

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('dragover');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('dragover');
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('dragover');
        
        if (e.dataTransfer.files.length) {
            selectedFile = e.dataTransfer.files[0];
            document.getElementById('file-name-display').textContent = selectedFile.name;
        }
    });

    function handleFileSelect(event) {
        if (event.target.files.length) {
            selectedFile = event.target.files[0];
            document.getElementById('file-name-display').textContent = selectedFile.name;
        }
    }

    function registerBulk() {
        if (!selectedFile) {
            showAlert('Sila pilih fail CSV terlebih dahulu.', true);
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', selectedFile);

        fetch(`/registration/bulk`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let msg = `Berjaya mendaftar ${data.registered.length} pelajar.`;
                if (data.errors && data.errors.length) {
                    msg += ` Gagal memuat naik ${data.errors.length} pelajar.`;
                }
                showAlert(msg, data.errors && data.errors.length > 0);

                const noDataRow = document.getElementById('no-data-row');
                if (noDataRow) noDataRow.remove();

                const tbody = document.querySelector('#registered-students-table tbody');
                
                data.registered.forEach(student => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td></td>
                        <td>${student.matric_number}</td>
                        <td>${student.name}</td>
                        <td>${student.program || ''}</td>
                    `;
                    tbody.prepend(tr);
                });
                updateTableIndices();
                currentPage = 1;
                renderTablePagination();

                // Reset file input
                selectedFile = null;
                document.getElementById('csv_file').value = '';
                document.getElementById('file-name-display').textContent = '';
            } else {
                showAlert(data.message || 'Ralat memproses pendaftaran pukal.', true);
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('Ralat memuat naik fail.', true);
        });
    }

    // Initialize pagination on DOM load
    document.addEventListener('DOMContentLoaded', () => {
        renderTablePagination();
    });
</script>
@endsection
