@extends('layouts.appikom')

@section('title', '- Penghantaran Tugasan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styleTugasanPelajar.css') }}?v={{ time() }}">

@if (Auth::user()->fld_user_role == 3)
<div class="hantar-tugasan-wrapper">

    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger alert-danger-center">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Select -->
    <div class="filter-controls">
        <select id="statusFilter" onchange="filterTasks()">
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
                <div class="task-group-section" data-group-status="{{ $groupId }}" style="display: {{ $group['tasks']->isEmpty() ? 'none' : 'block' }};">
                    @if(!$group['tasks']->isEmpty())
                        <div class="group-header">
                            <h3><i class="fas fa-tasks text-indigo-500"></i> {{ $group['title'] }}</h3>
                            <div class="group-header-divider"></div>
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
                                        
                                        <div class="task-type-container">
                                            <span class="task-type-badge"><i class="fas fa-users"></i> {{ $tgs->fld_tgs_jenis }}</span>
                                        </div>

                                        <p class="due-date"><i class="fas fa-calendar-times"></i> Tarikh Tutup: {{ \Carbon\Carbon::parse($tgs->fld_tgs_tarikh)->format('d M Y') }}</p>
                                        <p class="desc">{{ $tgs->fld_tgs_desc }}</p>

                                        @if($tgs->fld_tgs_file)
                                            <p class="lampiran">
                                                <a href="{{ route('file.tugasan', $tgs->fld_tgs_file) }}" target="_blank">
                                                    <i class="fas fa-paperclip"></i> Muat Turun Lampiran Tugasan
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="task-action">
                                        @if($hasSubmitted)
                                            <div class="task-action-buttons">
                                                <button class="btn-hantar" onclick="confirmResubmit({{ $tgs->fld_tgs_id }}, '{{ addslashes($tgs->fld_tgs_nama) }}')">Hantar Semula</button>
                                                <a class="btn-semak" href="{{ route('file.penghantaran', $tgs->penghantaran->first()->fld_pgh_fail ?? '') }}" target="_blank">Semak Fail Tugasan</a>
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

            <div class="form-group" id="groupMembersGroup" style="display: none;">
                <label><i class="fas fa-users"></i> Ahli Kumpulan</label>
                <div class="btn-select-members-container">
                    <button type="button" class="btn-select-members" onclick="openMembersModal()"><i class="fas fa-user-plus"></i> Pilih Ahli Kumpulan</button>
                </div>
                <!-- Container to show selected chips -->
                <div id="selectedMembersContainer">
                    <span id="noMembersSelectedText">Tiada ahli dipilih (Hanya anda)</span>
                </div>
                <small class="members-note">* Hanya pelajar yang belum menghantar akan dipaparkan. Anda tidak perlu memilih diri sendiri.</small>

                <!-- Hidden Modal for Member Selection -->
                <div id="membersModal" class="members-modal" style="display:none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><i class="fas fa-users"></i> Pilih Ahli Kumpulan</h3>
                            <button type="button" class="btn-close-modal" onclick="closeMembersModal()">&times;</button>
                        </div>
                        
                        <input type="text" id="memberSearch" placeholder="Cari nama atau no matrik..." onkeyup="filterMembers()">

                        <div class="members-checkbox-list" id="modalCheckboxList">
                            @foreach($rakanSigs as $rakan)
                                <div class="member-checkbox-item" data-nomat="{{ $rakan->fld_pel_nomat }}">
                                    <input type="checkbox" id="member_{{ $rakan->fld_pel_nomat }}" name="group_members[]" value="{{ $rakan->fld_pel_nomat }}" class="member-checkbox" data-name="{{ $rakan->pengguna->fld_user_nama }}">
                                    <label for="member_{{ $rakan->fld_pel_nomat }}" class="member-label">
                                        {{ $rakan->pengguna->fld_user_nama }} <br>
                                        <small>{{ $rakan->fld_pel_nomat }}</small>
                                    </label>
                                </div>
                            @endforeach
                            @if($rakanSigs->isEmpty())
                                <div class="empty-rakan-msg">Tiada rakan SIG lain.</div>
                            @endif
                            <div id="noAvailableMembersMsg" style="display: none;">Semua rakan SIG telah menghantar atau ditag untuk tugasan ini.</div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn-modal-cancel" onclick="closeMembersModal()">Batal</button>
                            <button type="button" class="btn-modal-confirm" onclick="confirmMembers()"><i class="fas fa-check"></i> Selesai</button>
                        </div>
                    </div>
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

<!-- Custom Confirmation Modal -->
<div id="confirmResubmitModal" class="members-modal" style="display:none;">
    <div class="modal-content confirm-resubmit-content">
        <div class="confirm-icon-wrapper">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="confirm-title">Amaran Hantar Semula</h3>
        <p class="confirm-message">
            Tindakan ini akan:<br><br>
            • <strong>Memadam</strong> penghantaran lama ANDA<br>
            • <strong>Memadam</strong> penghantaran semua ahli kumpulan yang ditag sebelum ini<br><br>
            Selepas menghantar semula, anda perlu <strong>TAG SEMULA</strong> semua ahli kumpulan. Teruskan?
        </p>
        <div class="modal-footer confirm-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeConfirmModal()">Batal</button>
            <button type="button" class="btn-modal-confirm danger" onclick="proceedResubmit()">Ya, Teruskan</button>
        </div>
    </div>
</div>

<script>
    const submittedMap = {};
    @foreach($tugasans as $tgs)
        submittedMap[{{ $tgs->fld_tgs_id }}] = [
            @foreach($tgs->penghantaran as $pgh)
                "{{ $pgh->fld_pel_nomat }}",
            @endforeach
        ];
    @endforeach
    
    const taskTypes = {};
    @foreach($tugasans as $tgs)
        taskTypes[{{ $tgs->fld_tgs_id }}] = "{{ $tgs->fld_tgs_jenis }}";
    @endforeach

    let resubmitId = null;
    let resubmitTitle = null;

    function confirmResubmit(id, title) {
        resubmitId = id;
        resubmitTitle = title;
        document.getElementById('confirmResubmitModal').style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirmResubmitModal').style.display = 'none';
        resubmitId = null;
        resubmitTitle = null;
    }

    function proceedResubmit() {
        if (!resubmitId) return;
        
        const btnConfirm = document.querySelector('#confirmResubmitModal .btn-modal-confirm');
        const originalText = btnConfirm.innerText;
        btnConfirm.innerText = 'Memadam...';
        btnConfirm.disabled = true;

        fetch(`{{ url('tugasanPelajar') }}/${resubmitId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnConfirm.innerText = originalText;
            btnConfirm.disabled = false;
            closeConfirmModal();
            
            if (data.success) {
                // Reload the page to refresh the view
                window.location.reload();
            } else {
                alert('Ralat: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnConfirm.innerText = originalText;
            btnConfirm.disabled = false;
            alert('Ralat sistem semasa memadam penghantaran lama.');
        });
    }

    function showHantarForm(id, title) {
        document.getElementById('tasksMainContainer').style.display = 'none';
        document.querySelector('.filter-controls').style.display = 'none';
        document.getElementById('hantarFormSection').style.display = 'block';
        document.getElementById('taskNamePlaceholder').innerText = title;
        document.getElementById('tugasan_id').value = id;

        if (taskTypes[id] === 'Berkumpulan') {
            document.getElementById('groupMembersGroup').style.display = 'block';
            
            // clear selected chips
            renderSelectedChips();
            
            // hide those who submitted
            const items = document.querySelectorAll('.member-checkbox-item');
            let availableCount = 0;
            items.forEach(item => {
                const checkbox = item.querySelector('.member-checkbox');
                if (submittedMap[id] && submittedMap[id].includes(item.getAttribute('data-nomat'))) {
                    item.style.display = 'none';
                    item.classList.add('unavailable-member');
                    checkbox.checked = false;
                } else {
                    item.style.display = 'flex';
                    item.classList.remove('unavailable-member');
                    availableCount++;
                }
            });

            if(availableCount === 0) {
                document.getElementById('noAvailableMembersMsg').style.display = 'block';
            } else {
                document.getElementById('noAvailableMembersMsg').style.display = 'none';
            }
        } else {
            document.getElementById('groupMembersGroup').style.display = 'none';
            // uncheck all
            const checkboxes = document.querySelectorAll('.member-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            renderSelectedChips();
        }
    }

    function openMembersModal() {
        document.getElementById('membersModal').style.display = 'flex';
        document.getElementById('memberSearch').value = '';
        filterMembers(); // reset filter
    }

    function closeMembersModal() {
        document.getElementById('membersModal').style.display = 'none';
    }

    function confirmMembers() {
        closeMembersModal();
        renderSelectedChips();
    }

    function filterMembers() {
        const input = document.getElementById('memberSearch').value.toLowerCase();
        const items = document.querySelectorAll('.member-checkbox-item');
        
        items.forEach(item => {
            if(item.classList.contains('unavailable-member')) return; // keep hidden if already submitted
            
            const label = item.querySelector('.member-label').innerText.toLowerCase();
            if (label.includes(input)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function renderSelectedChips() {
        const container = document.getElementById('selectedMembersContainer');
        const checkboxes = document.querySelectorAll('.member-checkbox:checked');
        
        container.innerHTML = ''; // clear current

        if (checkboxes.length === 0) {
            container.innerHTML = '<span id="noMembersSelectedText">Tiada ahli dipilih (Hanya anda)</span>';
            return;
        }

        checkboxes.forEach(cb => {
            const name = cb.getAttribute('data-name');
            const nomat = cb.value;
            
            const chip = document.createElement('div');
            chip.className = 'member-chip';
            
            chip.innerHTML = `
                <span>${name}</span>
                <i class="fas fa-times member-chip-close" onclick="removeMember('${nomat}')"></i>
            `;
            container.appendChild(chip);
        });
    }

    function removeMember(nomat) {
        const cb = document.getElementById('member_' + nomat);
        if (cb) {
            cb.checked = false;
            renderSelectedChips();
        }
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
