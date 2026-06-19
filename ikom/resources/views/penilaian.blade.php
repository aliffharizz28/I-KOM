@extends('layouts.appikom')

@section('title', '- Penilaian Markah')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylepenilaian.css') }}?v={{ filemtime(public_path('css/stylepenilaian.css')) }}">

<div class="penilaian-wrapper">

    <!-- Page Header: SIG Logo + Name -->
    <div class="sig-header">
        <div class="sig-header-left">
            <div class="sig-logo-wrapper">
                @if($sigLogo)
                    <img src="{{ asset($sigLogo) }}" alt="{{ $sigNama }}" class="sig-logo">
                @else
                    <div class="sig-logo-placeholder">
                        <i class="fas fa-users"></i>
                    </div>
                @endif
            </div>
            <div class="sig-header-text">
                <h1>{{ $sigNama }}</h1>
                <p>Penilaian Markah Pelajar</p>
            </div>
        </div>

        <div class="sig-actions">
            <a href="{{ route('penilaian.export') }}" class="btn-export">
                <i class="fas fa-file-excel"></i> Muat Turun Markah
            </a>
        </div>
    </div>

    <!-- Notifications -->
    <div id="successAlert" class="success-alert">
        <i class="fas fa-check-circle"></i> <span id="successMsg"></span>
    </div>
    <div id="errorAlert" class="error-alert">
        <i class="fas fa-exclamation-circle"></i> <span id="errorMsg"></span>
    </div>

    <!-- Controls Row -->
    <div class="controls-row">
        <!-- Search Bar -->
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari pelajar mengikut nama atau no. matrik..." oninput="filterStudents()">
            <span class="search-count" id="searchCount">{{ $pelajars->count() }} pelajar</span>
        </div>

        <!-- Publish Controls -->
        <div class="publish-controls">
            <span class="publish-label"><i class="fas fa-bullhorn"></i> Terbitkan Markah:</span>
            <select id="publishSelect" class="publish-select" onchange="promptPublishConfirm(this)">
                <option value="0" {{ $publishStatus == 0 ? 'selected' : '' }}>Draf (Tidak Diterbitkan Markah)</option>
                <option value="1" {{ $publishStatus == 1 ? 'selected' : '' }}>Fasa 1 (60% Penilaian Berterusan)</option>
                <option value="2" {{ $publishStatus == 2 ? 'selected' : '' }}>Fasa 2 (100% Markah Keseluruhan)</option>
            </select>
            <span id="publishLoading" style="display: none; color: var(--primary-blue);"><i class="fas fa-spinner fa-spin"></i></span>
        </div>
    </div>

    @if($pelajars->isEmpty())
        <!-- Empty State -->
        <div class="empty-state-card">
            <i class="fas fa-inbox"></i>
            <h3>Tiada Pelajar</h3>
            <p>Belum ada pelajar yang berdaftar dalam SIG anda.</p>
        </div>
    @else
        <!-- Student List -->
        <div class="student-list">
            @foreach($pelajars as $index => $pelajar)
            <div class="student-card" style="animation-delay: {{ $index * 0.05 }}s" data-name="{{ strtolower($pelajar->pengguna->fld_user_nama ?? '') }}" data-matric="{{ strtolower($pelajar->fld_pel_nomat) }}">
                <div class="student-info">
                    <div class="student-avatar">
                        <img src="{{ $pelajar->final_pic_url }}" 
                             alt="foto" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-placeholder" style="display: none;">
                            {{ strtoupper(substr($pelajar->pengguna->fld_user_nama ?? 'P', 0, 1)) }}
                        </div>
                    </div>
                    <div class="student-details">
                        <h4 class="student-name">{{ $pelajar->pengguna->fld_user_nama ?? 'Tiada Nama' }}</h4>
                        <div class="student-meta">
                            <span class="meta-item">
                                <i class="fas fa-id-badge"></i> {{ $pelajar->fld_pel_nomat }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-graduation-cap"></i> Tahun {{ $pelajar->fld_pel_tahun }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-laptop-code"></i> {{ $pelajar->fld_pel_jurusan }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="student-actions">
                    <a href="{{ route('penilaian.markah', $pelajar->fld_pel_nomat) }}" class="btn-nilai" title="Nilai pelajar ini">
                        <i class="fas fa-pen-alt"></i> Nilai
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div id="pagination-controls" class="pagination-footer" style="margin-top: 24px;"></div>

        <!-- Empty search result (shown by JS) -->
        <div id="emptySearchState" class="empty-state-card" style="display:none;">
            <i class="fas fa-search"></i>
            <h3>Tiada Hasil Carian</h3>
            <p>Tiada pelajar ditemui dengan kata kunci tersebut.</p>
        </div>
    @endif
</div>

<!-- Confirmation Modal -->
<div id="confirmPublishModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Pengesahan Terbitan</h3>
        <p>Adakah anda pasti untuk menukar status penerbitan markah kepada <strong id="publishStatusText"></strong>?</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeConfirmModal()">Batal</button>
            <button class="btn-confirm" onclick="executePublish()">Ya, Terbitkan</button>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    const cardsPerPage = 10;

    function renderPagination() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('.student-card'));
        
        // Filter cards matching query
        const matchedCards = cards.filter(card => {
            const name = card.getAttribute('data-name') || '';
            const matric = card.getAttribute('data-matric') || '';
            return name.includes(query) || matric.includes(query);
        });

        const totalRows = matchedCards.length;
        const totalPages = Math.ceil(totalRows / cardsPerPage);
        const paginationControls = document.getElementById('pagination-controls');

        if (totalRows === 0) {
            cards.forEach(card => card.style.display = 'none');
            if (paginationControls) paginationControls.style.display = 'none';
            
            const emptySearch = document.getElementById('emptySearchState');
            if (emptySearch) {
                emptySearch.style.display = query.length > 0 ? '' : 'none';
            }
            document.getElementById('searchCount').textContent = '0 pelajar';
            return;
        }

        const emptySearch = document.getElementById('emptySearchState');
        if (emptySearch) emptySearch.style.display = 'none';

        if (paginationControls) paginationControls.style.display = 'flex';

        // Ensure currentPage is within range
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Hide all cards first, then only show the slice for the current page
        cards.forEach(card => card.style.display = 'none');

        const start = (currentPage - 1) * cardsPerPage;
        const end = start + cardsPerPage;
        
        matchedCards.forEach((card, index) => {
            if (index >= start && index < end) {
                card.style.display = '';
            }
        });

        // Update search count
        document.getElementById('searchCount').textContent = totalRows + ' pelajar';

        // Render pagination buttons
        const startItem = (currentPage - 1) * cardsPerPage + 1;
        const endItem = Math.min(currentPage * cardsPerPage, totalRows);
        
        let html = `
            <div class="pagination-info">
                Menunjukkan <strong>${startItem}</strong> hingga <strong>${endItem}</strong> daripada <strong>${totalRows}</strong> pelajar
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
        renderPagination();
    }

    function filterStudents() {
        currentPage = 1;
        renderPagination();
    }

    // Initialize pagination on DOM load
    document.addEventListener('DOMContentLoaded', () => {
        renderPagination();
    });

    let pendingPublishValue = null;
    let previousPublishValue = document.getElementById('publishSelect').value;

    function promptPublishConfirm(selectElement) {
        pendingPublishValue = selectElement.value;
        const selectedText = selectElement.options[selectElement.selectedIndex].text;
        
        document.getElementById('publishStatusText').textContent = selectedText;
        document.getElementById('confirmPublishModal').classList.add('active');
    }

    function closeConfirmModal() {
        document.getElementById('confirmPublishModal').classList.remove('active');
        // Revert the select box to its previous value since they cancelled
        document.getElementById('publishSelect').value = previousPublishValue;
        pendingPublishValue = null;
    }

    function executePublish() {
        document.getElementById('confirmPublishModal').classList.remove('active');
        const status = pendingPublishValue;
        
        const loading = document.getElementById('publishLoading');
        loading.style.display = 'inline-block';
        
        // Hide previous alerts
        document.getElementById('successAlert').style.display = 'none';
        document.getElementById('errorAlert').style.display = 'none';
        
        fetch('{{ route("penilaian.publish") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            if (data.success) {
                previousPublishValue = status; // Update successful state
                document.getElementById('successMsg').textContent = 'Status penerbitan markah berjaya dikemaskini.';
                document.getElementById('successAlert').style.display = 'block';
                
                setTimeout(() => {
                    document.getElementById('successAlert').style.display = 'none';
                }, 5000);
            } else {
                document.getElementById('publishSelect').value = previousPublishValue; // Revert
                document.getElementById('errorMsg').textContent = data.message || 'Ralat berlaku semasa kemaskini.';
                document.getElementById('errorAlert').style.display = 'block';
            }
        })
        .catch(err => {
            loading.style.display = 'none';
            document.getElementById('publishSelect').value = previousPublishValue; // Revert
            document.getElementById('errorMsg').textContent = 'Ralat rangkaian. Sila cuba lagi.';
            document.getElementById('errorAlert').style.display = 'block';
        });
    }
</script>
@endsection
