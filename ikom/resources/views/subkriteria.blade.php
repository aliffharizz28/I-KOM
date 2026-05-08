@extends('layouts.appikom')

@section('title', '- Rubrik Pemarkahan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylesubkriteria.css') }}?v={{ filemtime(public_path('css/stylesubkriteria.css')) }}">

<div class="subkriteria-wrapper">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Top Action Bar -->
    <div class="action-bar">
        <button type="button" class="btn-action-bar btn-create" onclick="openCreateModal()">
            <i class="fas fa-plus-circle"></i> Cipta Subkriteria
        </button>
        <button type="button" class="btn-action-bar btn-edit-toggle" id="btnEditToggle" onclick="toggleEditMode()">
            <i class="fas fa-pen"></i> <span id="editToggleText">Sunting Rubrik</span>
        </button>
    </div>

    <!-- Create Subkriteria Modal -->
    <div id="createSubkriteriaModal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-content-wide">
            <div class="modal-header">
                <h3><i class="fas fa-layer-group"></i> Cipta Subkriteria Baru</h3>
                <button type="button" class="modal-close-btn" onclick="closeCreateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="createSubkriteriaForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newSubNama"><i class="fas fa-tag"></i> Nama Subkriteria</label>
                        <input type="text" id="newSubNama" name="fld_sub_nama" class="modal-input" placeholder="Contoh: Kemahiran Komunikasi Lisan" required>
                    </div>

                    <!-- Descriptions Section -->
                    <div class="form-group">
                        <label><i class="fas fa-list-ol"></i> Penerangan Pemarkahan</label>
                        <p class="desc-hint">Tambah kriteria penilaian untuk subkriteria ini. Setiap penerangan mempunyai markah maksimum tersendiri.</p>
                        <div id="descriptionsList" class="descriptions-list"></div>
                        <button type="button" class="btn-add-desc" onclick="addDescriptionRow()">
                            <i class="fas fa-plus"></i> Tambah Penerangan
                        </button>
                    </div>

                    <div id="createModalError" class="modal-error" style="display:none;"></div>
                    <div id="createModalSuccess" class="modal-success" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeCreateModal()">Batal</button>
                    <button type="submit" class="btn-modal-save" id="btnSaveSubkriteria">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form action="{{ route('subkriteria.store') }}" method="POST" id="subkriteriaForm" onsubmit="return validateForm()">
        @csrf
        <div class="card-container">
        @foreach($kriterias as $kriteria)
        <div class="subkriteria-card glass-effect">
            <h3 class="subkriteria-header">
                {{ $kriteria->fld_krit_nama }}
                <span class="kriteria-markah">{{ $kriteria->fld_krit_markah }}%</span>
            </h3>

            <!-- Progress Bar -->
            <div class="progress-container">
                <div id="progress-bar-{{ $kriteria->fld_krit_id }}" class="progress-bar"></div>
            </div>
            <div class="progress-text" id="progress-text-{{ $kriteria->fld_krit_id }}">0 / {{ $kriteria->fld_krit_markah }}</div>

            <!-- VIEW MODE: Read-only display -->
            <div id="view-{{ $kriteria->fld_krit_id }}" class="subkriteria-view-list">
                @forelse($kriteria->subkriteria as $sub)
                <div class="subkriteria-view-card">
                    <div class="subkriteria-view-item" onclick="this.parentElement.classList.toggle('expanded')">
                        <div class="view-sub-left">
                            <i class="fas fa-chevron-right view-expand-icon"></i>
                            <span class="view-sub-nama">{{ $sub->fld_sub_nama }}</span>
                        </div>
                        <span class="view-sub-markah">{{ $sub->fld_sub_markah ?? 0 }}%</span>
                    </div>
                    @if($sub->descriptions->count() > 0)
                    <div class="view-desc-list">
                        @foreach($sub->descriptions as $desc)
                        <div class="view-desc-item">
                            <span class="view-desc-text">{{ $desc->fld_desc_text }}</span>
                            <span class="view-desc-markah">/ {{ $desc->fld_desc_markah }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="subkriteria-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Tiada subkriteria</span>
                </div>
                @endforelse
            </div>

            <!-- EDIT MODE: Editable inputs (hidden by default) -->
            <div id="list-{{ $kriteria->fld_krit_id }}" class="subkriteria-list edit-mode-only" style="display:none;">
                @foreach($kriteria->subkriteria as $sub)
                <div class="subkriteria-item">
                    <input type="hidden" name="krit_id[]" value="{{ $kriteria->fld_krit_id }}">
                    <select name="sub_id[]" class="subkriteria-select form-control" required onchange="refreshAllDropdowns()">
                        <option value="" disabled>Pilih Subkriteria</option>
                        @foreach($subkriterias as $option)
                            <option value="{{ $option->fld_sub_id }}" {{ $sub->fld_sub_id == $option->fld_sub_id ? 'selected' : '' }}>
                                {{ $option->fld_sub_nama }}
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="markah[]" class="subkriteria-input form-control" value="{{ $sub->fld_sub_markah }}" min="0" max="{{ $kriteria->fld_krit_markah }}" required oninput="if(parseFloat(this.value) > {{ $kriteria->fld_krit_markah }}) { alert('Markah tidak boleh melebihi markah penuh kriteria ({{ $kriteria->fld_krit_markah }}%)'); this.value = {{ $kriteria->fld_krit_markah }}; } updateProgress('{{ $kriteria->fld_krit_id }}', {{ $kriteria->fld_krit_markah }});">
                    <button type="button" class="btn-remove-subkriteria" onclick="this.parentElement.remove(); updateProgress('{{ $kriteria->fld_krit_id }}', {{ $kriteria->fld_krit_markah }}); refreshAllDropdowns();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @endforeach
            </div>

            <!-- Add button per card (only visible in edit mode) -->
            <button type="button" class="btn-add-subkriteria edit-mode-only" style="display:none;" onclick="addSubkriteria('{{ $kriteria->fld_krit_id }}', {{ $kriteria->fld_krit_markah }})">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </div>
        @endforeach
        </div>

        <!-- Save button (only visible in edit mode) -->
        <div id="saveButtonContainer" class="save-button-container" style="display:none;">
            <button type="submit" class="btn-save-bottom">
                <i class="fas fa-save"></i> Simpan Rubrik Penilaian
            </button>
        </div>
    </form>
</div>

<script>
    const allSubkriterias = @json($subkriterias);
    const kriteriaData = {
        @foreach($kriterias as $kriteria)
            '{{ $kriteria->fld_krit_id }}': {{ $kriteria->fld_krit_markah }},
        @endforeach
    };

    let isEditMode = false;

    // ===== Edit Mode Toggle =====
    function toggleEditMode() {
        isEditMode = !isEditMode;
        const btn = document.getElementById('btnEditToggle');
        const text = document.getElementById('editToggleText');
        const saveContainer = document.getElementById('saveButtonContainer');

        // Toggle all edit-mode elements
        document.querySelectorAll('.edit-mode-only').forEach(el => {
            el.style.display = isEditMode ? '' : 'none';
        });

        // Toggle all view-mode elements
        document.querySelectorAll('.subkriteria-view-list').forEach(el => {
            el.style.display = isEditMode ? 'none' : '';
        });

        // Toggle save button
        saveContainer.style.display = isEditMode ? '' : 'none';

        // Update toggle button appearance
        if (isEditMode) {
            btn.classList.add('active');
            text.textContent = 'Batal Edit';
            btn.querySelector('i').className = 'fas fa-times';
        } else {
            btn.classList.remove('active');
            text.textContent = 'Edit Rubrik';
            btn.querySelector('i').className = 'fas fa-pen';
        }

        if (isEditMode) {
            refreshAllDropdowns();
        }
    }

    function addSubkriteria(id, maxMark) {
        const listContainer = document.getElementById('list-' + id);

        // Create wrapper for the new subkriteria item
        const itemRow = document.createElement('div');
        itemRow.className = 'subkriteria-item';

        // Create Select Dropdown
        const selectBox = document.createElement('select');
        selectBox.name = 'sub_id[]';
        selectBox.className = 'subkriteria-select form-control';
        selectBox.required = true;

        let optionsHtml = '<option value="" disabled selected>Pilih Subkriteria</option>';
        allSubkriterias.forEach(sub => {
            optionsHtml += `<option value="${sub.fld_sub_id}">${sub.fld_sub_nama}</option>`;
        });
        selectBox.innerHTML = optionsHtml;
        selectBox.addEventListener('change', function() {
            refreshAllDropdowns();
        });

        // Create Number Input
        const numberInput = document.createElement('input');
        numberInput.type = 'number';
        numberInput.name = 'markah[]';
        numberInput.className = 'subkriteria-input form-control';
        numberInput.placeholder = '0';
        numberInput.min = '0';
        numberInput.max = maxMark;
        numberInput.required = true;

        // Enforce max mark logic & update progress
        numberInput.addEventListener('input', function() {
            if (parseFloat(this.value) > maxMark) {
                alert('Markah tidak boleh melebihi markah penuh kriteria (' + maxMark + '%)');
                this.value = maxMark;
            }
            updateProgress(id, maxMark);
        });

        // Remove button for each row
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-subkriteria';
        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
        removeBtn.onclick = function() {
            itemRow.remove();
            updateProgress(id, maxMark);
            refreshAllDropdowns();
        };

        // Hidden input for Krit ID
        const hiddenKrit = document.createElement('input');
        hiddenKrit.type = 'hidden';
        hiddenKrit.name = 'krit_id[]';
        hiddenKrit.value = id;

        // Append elements to row
        itemRow.appendChild(hiddenKrit);
        itemRow.appendChild(selectBox);
        itemRow.appendChild(numberInput);
        itemRow.appendChild(removeBtn);

        // Append row to the list container
        listContainer.appendChild(itemRow);
        refreshAllDropdowns();
    }

    // Remove already-selected options from dropdowns across ALL cards
    function refreshAllDropdowns() {
        // Collect ALL selected values across every card
        const allSelects = document.querySelectorAll('.subkriteria-select');
        const selectedValues = [];
        allSelects.forEach(sel => {
            if (sel.value) {
                selectedValues.push(sel.value);
            }
        });

        // Rebuild options for each select, excluding globally taken values
        allSelects.forEach(sel => {
            const currentVal = sel.value;
            sel.innerHTML = '<option value="" disabled' + (!currentVal ? ' selected' : '') + '>Pilih Subkriteria</option>';

            allSubkriterias.forEach(sub => {
                const val = String(sub.fld_sub_id);
                // Only add if it's the current selection OR not taken by any other dropdown
                if (val === currentVal || !selectedValues.includes(val)) {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = sub.fld_sub_nama;
                    if (val === currentVal) opt.selected = true;
                    sel.appendChild(opt);
                }
            });
        });
    }

    function updateProgress(id, maxMark) {
        const listContainer = document.getElementById('list-' + id);
        const inputs = listContainer.querySelectorAll('.subkriteria-input');
        let totalMarkah = 0;

        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if(!isNaN(val)) {
                totalMarkah += val;
            }
        });

        const progressBar = document.getElementById('progress-bar-' + id);
        const progressText = document.getElementById('progress-text-' + id);
        const progressContainer = progressBar.parentElement;

        let percentage = (totalMarkah / maxMark) * 100;
        let displayPercentage = percentage > 100 ? 100 : percentage;

        let color = '#ef4444'; // Red for < 50%
        if (totalMarkah > maxMark) {
            color = '#dc2626'; // Bright red for exceeded
            progressText.innerHTML = '<span style="color:#dc2626;font-weight:700;"><i class="fas fa-exclamation-triangle"></i> ' + totalMarkah + ' / ' + maxMark + ' (Melebihi had!)</span>';
            progressContainer.style.boxShadow = '0 0 8px rgba(220, 38, 38, 0.5)';
        } else {
            if (percentage >= 100) {
                color = '#10b981'; // Green for 100%
            } else if (percentage >= 50) {
                color = '#f59e0b'; // Yellow for 50-99%
            }
            progressText.innerText = totalMarkah + ' / ' + maxMark;
            progressContainer.style.boxShadow = 'none';
        }

        progressBar.style.width = displayPercentage + '%';
        progressBar.style.backgroundColor = color;
    }

    function validateForm() {
        let isValid = true;
        let errorMessages = [];

        for (const [kritId, maxMark] of Object.entries(kriteriaData)) {
            const listContainer = document.getElementById('list-' + kritId);
            if (!listContainer) continue;
            const inputs = listContainer.querySelectorAll('.subkriteria-input');
            let totalMarkah = 0;

            inputs.forEach(input => {
                let val = parseFloat(input.value);
                if (!isNaN(val)) {
                    totalMarkah += val;
                }
            });

            if (totalMarkah > maxMark) {
                isValid = false;
                // Find the kriteria name from the card header
                const card = listContainer.closest('.subkriteria-card');
                const name = card.querySelector('.subkriteria-header').childNodes[1]?.textContent?.trim() || 'Kriteria';
                errorMessages.push(name + ': Jumlah markah (' + totalMarkah + ') melebihi had (' + maxMark + '%)');
            }
        }

        if (!isValid) {
            alert('Tidak boleh simpan! Sila betulkan markah berikut:\n\n' + errorMessages.join('\n'));
        }
        return isValid;
    }

    document.addEventListener('DOMContentLoaded', function() {
        @foreach($kriterias as $kriteria)
            updateProgress('{{ $kriteria->fld_krit_id }}', {{ $kriteria->fld_krit_markah }});
        @endforeach
        refreshAllDropdowns();
    });

    // ===== Create Subkriteria Modal Logic =====
    let descCounter = 0;

    function addDescriptionRow() {
        descCounter++;
        const list = document.getElementById('descriptionsList');
        const row = document.createElement('div');
        row.className = 'desc-row';
        row.innerHTML = `
            <input type="text" class="desc-text-input" placeholder="Penerangan..." data-desc-id="${descCounter}" required>
            <div class="desc-markah-wrapper">
                <span class="desc-markah-prefix">/</span>
                <input type="number" class="desc-markah-input" value="5" min="1" max="100" data-desc-id="${descCounter}" required>
            </div>
            <button type="button" class="btn-remove-desc" onclick="this.parentElement.remove()">
                <i class="fas fa-trash"></i>
            </button>
        `;
        list.appendChild(row);
        row.querySelector('.desc-text-input').focus();
    }

    function openCreateModal() {
        document.getElementById('createSubkriteriaModal').style.display = 'flex';
        document.getElementById('newSubNama').value = '';
        document.getElementById('descriptionsList').innerHTML = '';
        document.getElementById('createModalError').style.display = 'none';
        document.getElementById('createModalSuccess').style.display = 'none';
        document.getElementById('btnSaveSubkriteria').disabled = false;
        descCounter = 0;
        // Add one default description row
        addDescriptionRow();
        setTimeout(() => document.getElementById('newSubNama').focus(), 100);
    }

    function closeCreateModal() {
        document.getElementById('createSubkriteriaModal').style.display = 'none';
    }

    // Close modal when clicking overlay background
    document.getElementById('createSubkriteriaModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCreateModal();
    });

    // Handle form submission via AJAX
    document.getElementById('createSubkriteriaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const nama = document.getElementById('newSubNama').value.trim();
        const errorDiv = document.getElementById('createModalError');
        const successDiv = document.getElementById('createModalSuccess');
        const saveBtn = document.getElementById('btnSaveSubkriteria');

        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';

        if (!nama) {
            errorDiv.textContent = 'Sila masukkan nama subkriteria.';
            errorDiv.style.display = 'block';
            return;
        }

        // Collect descriptions
        const descRows = document.querySelectorAll('#descriptionsList .desc-row');
        const descriptions = [];
        let descValid = true;
        descRows.forEach(row => {
            const text = row.querySelector('.desc-text-input').value.trim();
            const markah = parseInt(row.querySelector('.desc-markah-input').value);
            if (!text) {
                descValid = false;
            }
            descriptions.push({ text: text, markah: markah || 5 });
        });

        if (descriptions.length === 0) {
            errorDiv.textContent = 'Sila tambah sekurang-kurangnya satu penerangan.';
            errorDiv.style.display = 'block';
            return;
        }

        if (!descValid) {
            errorDiv.textContent = 'Semua penerangan mesti diisi.';
            errorDiv.style.display = 'block';
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('{{ route("subkriteria.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fld_sub_nama: nama, descriptions: descriptions })
        })
        .then(response => {
            if (!response.ok && response.status === 422) {
                return response.json().then(data => {
                    const firstError = Object.values(data.errors || {})[0];
                    throw new Error(firstError ? firstError[0] : 'Ralat pengesahan.');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                successDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                successDiv.style.display = 'block';

                // Add the new subkriteria to the global list so dropdowns update
                allSubkriterias.push({
                    fld_sub_id: String(data.subkriteria.fld_sub_id),
                    fld_sub_nama: data.subkriteria.fld_sub_nama
                });
                refreshAllDropdowns();

                // Reset and close after a short delay
                document.getElementById('newSubNama').value = '';
                setTimeout(() => {
                    closeCreateModal();
                    successDiv.style.display = 'none';
                }, 1200);
            } else {
                errorDiv.textContent = data.message || 'Ralat berlaku. Sila cuba lagi.';
                errorDiv.style.display = 'block';
            }
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        })
        .catch(err => {
            errorDiv.textContent = err.message || 'Ralat rangkaian. Sila cuba lagi.';
            errorDiv.style.display = 'block';
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        });
    });
</script>
@endsection
