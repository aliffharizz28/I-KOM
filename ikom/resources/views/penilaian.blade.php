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
            <select id="publishSelect" class="publish-select" onchange="updatePublishStatus(this.value)">
                <option value="0" {{ $publishStatus == 0 ? 'selected' : '' }}>Draf (Tidak Diterbit)</option>
                <option value="1" {{ $publishStatus == 1 ? 'selected' : '' }}>Fasa 1 (60% PB)</option>
                <option value="2" {{ $publishStatus == 2 ? 'selected' : '' }}>Fasa 2 (100% Keseluruhan)</option>
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
                        @if($pelajar->fld_pel_pic)
                            <img src="{{ asset('storage/' . $pelajar->fld_pel_pic) }}" alt="foto">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($pelajar->pengguna->fld_user_nama ?? 'P', 0, 1)) }}
                            </div>
                        @endif
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

        <!-- Empty search result (shown by JS) -->
        <div id="emptySearchState" class="empty-state-card" style="display:none;">
            <i class="fas fa-search"></i>
            <h3>Tiada Hasil Carian</h3>
            <p>Tiada pelajar ditemui dengan kata kunci tersebut.</p>
        </div>
    @endif
</div>

<script>
    function filterStudents() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.student-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const matric = card.getAttribute('data-matric') || '';
            const match = name.includes(query) || matric.includes(query);
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        document.getElementById('searchCount').textContent = visibleCount + ' pelajar';

        // Show/hide empty search state
        const emptySearch = document.getElementById('emptySearchState');
        const studentList = document.querySelector('.student-list');
        if (emptySearch && studentList) {
            if (visibleCount === 0 && query.length > 0) {
                emptySearch.style.display = '';
            } else {
                emptySearch.style.display = 'none';
            }
        }
    }

    function updatePublishStatus(status) {
        const loading = document.getElementById('publishLoading');
        loading.style.display = 'inline-block';
        
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
                // optionally show a toast
                alert('Status penerbitan markah berjaya dikemaskini.');
            } else {
                alert(data.message || 'Ralat berlaku.');
            }
        })
        .catch(err => {
            loading.style.display = 'none';
            alert('Ralat rangkaian.');
        });
    }
</script>
@endsection
