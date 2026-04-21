@extends('layouts.appikom')

@section('title', '- Penghantaran Tugasan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styleTugasanPelajar.css') }}?v={{ time() }}">

@if (Auth::user()->fld_user_role == 3)
<div class="hantar-tugasan-wrapper">
    @if(session('success'))
        <div class="alert-success" style="margin-bottom: 2rem; background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; text-align: center;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger" style="margin-bottom: 2rem; background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; text-align: center;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Filter Select -->
    <div class="filter-controls" style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
        <select id="statusFilter" onchange="filterTasks()" style="padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: inherit; font-size: 0.95rem; background-color: white; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <option value="all">Semua Status</option>
            <option value="belum">Belum Dihantar</option>
            <option value="telah">Telah Dihantar</option>
            <option value="tamat">Tamat Tempoh</option>
        </select>
    </div>

    <!--list of active tasks -->
    @php
        $tasksBelum = collect();
        $tasksTelah = collect();
        $tasksTamat = collect();

        foreach($tugasans as $tgs) {
            $hasSubmitted = $tgs->penghantaran->count() > 0;
            if ($hasSubmitted) {
                $tasksTelah->push($tgs);
            } else if ($tgs->fld_tgs_status == 'Aktif') {
                $tasksBelum->push($tgs);
            } else {
                $tasksTamat->push($tgs);
            }
        }

        $groupedTasks = [
            'belum' => ['title' => 'Belum Dihantar', 'tasks' => $tasksBelum],
            'telah' => ['title' => 'Telah Dihantar', 'tasks' => $tasksTelah],
            'tamat' => ['title' => 'Tamat Tempoh', 'tasks' => $tasksTamat],
        ];
    @endphp

    <div id="tasksMainContainer">
        @if($tugasans->isEmpty())
            <div class="empty-state">
                <i class="fas fa-clipboard-list fa-3x"></i>
                <p>Tiada tugasan yang direkodkan bagi SIG anda pada masa ini.</p>
            </div>
        @else
            @foreach($groupedTasks as $groupId => $group)
                <div class="task-group-section" data-group-status="{{ $groupId }}" style="display: {{ $group['tasks']->isEmpty() ? 'none' : 'block' }}; margin-bottom: 2.5rem;">
                    @if(!$group['tasks']->isEmpty())
                        <div class="group-header" style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0; padding-right: 1.5rem; color: #475569; font-size: 1.25rem; font-weight: 600;"><i class="fas fa-tasks text-indigo-500"></i> {{ $group['title'] }}</h3>
                            <div style="flex-grow: 1; height: 2px; background-color: #cbd5e1; border-radius: 1px;"></div>
                        </div>
                        <div class="tasks-grid">
                            @foreach($group['tasks'] as $tgs)
                                @php
                                    $hasSubmitted = $tgs->penghantaran->count() > 0;
                                @endphp
                                <div class="task-card" data-status="{{ $groupId }}">
                                    <div class="task-info">
                                        <h3>{{ $tgs->fld_tgs_nama }}</h3>
                                        @if($hasSubmitted)
                                            <span class="status success">Telah Dihantar</span>
                                        @else
                                            @if($tgs->fld_tgs_status == 'Aktif')
                                                <span class="status warning">Belum Dihantar</span>
                                            @else
                                                <span class="status danger">Tamat Tempoh / Tidak Aktif</span>
                                            @endif
                                        @endif
                                        <p class="due-date"><i class="fas fa-calendar-times"></i> Tarikh Tutup: {{ \Carbon\Carbon::parse($tgs->fld_tgs_tarikh)->format('d M Y') }}</p>
                                        <p class="desc">{{ $tgs->fld_tgs_desc }}</p>

                                        @if($tgs->fld_tgs_file)
                                            <p class="lampiran">
                                                <a href="{{ asset('lampiran_tugasan/'.$tgs->fld_tgs_file) }}" target="_blank">
                                                    <i class="fas fa-paperclip"></i> Muat Turun Lampiran Tugasan
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="task-action">
                                        @if($hasSubmitted)
                                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                                <button class="btn-hantar" onclick="showHantarForm({{ $tgs->fld_tgs_id }}, '{{ addslashes($tgs->fld_tgs_nama) }}')">Hantar Semula</button>
                                                <a class="btn-semak" href="{{ asset('lampiran_penghantaran/'.($tgs->penghantaran->first()->fld_pgh_fail ?? '')) }}" target="_blank" style="text-decoration:none; text-align:center; display:inline-flex; align-items:center; justify-content:center;">Semak Fail Tugasan</a>
                                            </div>
                                        @else
                                            @if($tgs->fld_tgs_status == 'Aktif')
                                                <button class="btn-hantar" onclick="showHantarForm({{ $tgs->fld_tgs_id }}, '{{ addslashes($tgs->fld_tgs_nama) }}')">Hantar Tugasan</button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <!-- Hidden Form Section for Submission -->
    <div class="glass-effect section-container" id="hantarFormSection" style="display: none;">
        <button type="button" onclick="hideHantarForm()" class="btn-close-section">
            <i class="fas fa-times"></i>
        </button>
        
        <h2 id="formTitle" class="section-title"><i class="fas fa-upload"></i> Hantar <span id="taskNamePlaceholder"></span></h2>

        <form id="hantarTugasanForm" action="{{ route('tugasanPelajar.store') }}" method="POST" enctype="multipart/form-data" class="tugasan-form">
            @csrf
            
            <input type="hidden" id="tugasan_id" name="tugasan_id" value="">

            <div class="form-group">
                <label for="tugasan_file"><i class="fas fa-file-archive"></i> Muat Naik Fail (ZIP/PDF/DOCX)</label>
                <div class="file-drop-area" id="fileDropArea">
                    <span class="fake-btn">Pilih Fail</span>
                    <span class="file-msg">atau seret dan lepas fail di sini</span>
                    <input class="file-input" type="file" id="tugasan_file" name="tugasan_file" accept=".zip,.pdf,.docx,.doc, .rar">
                </div>
            </div>



            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Hantar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showHantarForm(id, title) {
        document.getElementById('tasksMainContainer').style.display = 'none';
        document.querySelector('.filter-controls').style.display = 'none';
        document.getElementById('hantarFormSection').style.display = 'block';
        document.getElementById('taskNamePlaceholder').innerText = title;
        document.getElementById('tugasan_id').value = id;
    }

    function hideHantarForm() {
        document.getElementById('tasksMainContainer').style.display = 'block';
        document.querySelector('.filter-controls').style.display = 'flex';
        document.getElementById('hantarFormSection').style.display = 'none';
        
        // Ensure filter is applied when returning to grid
        filterTasks();
    }

    function filterTasks() {
        const filterValue = document.getElementById('statusFilter').value;
        const sections = document.querySelectorAll('.task-group-section');
        
        sections.forEach(section => {
            const status = section.getAttribute('data-group-status');
            const hasTasks = section.querySelector('.task-card') !== null;
            
            if (!hasTasks) {
                section.style.display = 'none';
                return;
            }

            if (filterValue === 'all') {
                section.style.display = 'block';
            } else if (filterValue === status) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    function showSemakDetail(id) {
        alert("Fungsi semak penghantaran untuk tugasan ID " + id + " akan dipaparkan di sini.");
    }

    // File input UX
    const fileInput = document.querySelector('.file-input');
    const fileMsg = document.querySelector('.file-msg');
    const dropArea = document.querySelector('.file-drop-area');
    
    if(fileInput && dropArea) {
        fileInput.addEventListener('change', function() {
            if(fileInput.files.length > 0) {
                fileMsg.innerText = fileInput.files[0].name;
            } else {
                fileMsg.innerText = 'atau seret dan lepas fail di sini';
            }
        });

        fileInput.addEventListener('dragenter', function() {
            dropArea.classList.add('is-active');
        });
        fileInput.addEventListener('dragleave', function() {
            dropArea.classList.remove('is-active');
        });
        fileInput.addEventListener('drop', function() {
            dropArea.classList.remove('is-active');
        });
    }
</script>
@else
    <div class="role-alert">
        Anda tidak mempunyai kebenaran untuk mengakses halaman ini. Halaman ini dikhaskan untuk pelajar (Role=3).
    </div>
@endif

@endsection
