@extends('layouts.appikom')

@section('title', '- Penilaian ' . ($pelajar->pengguna->fld_user_nama ?? 'Pelajar'))

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylepenilaianMarkah.css') }}?v={{ filemtime(public_path('css/stylepenilaianMarkah.css')) }}">

<div class="markah-wrapper">

    <!-- Back Button + Student Header -->
    <div class="markah-top-bar">
        <a href="{{ route('penilaian') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Student Info Card -->
    <div class="student-header-card">
        <div class="student-header-avatar">
            @if($pelajar->fld_pel_pic)
                <img src="{{ asset('storage/' . $pelajar->fld_pel_pic) }}" alt="foto">
            @else
                <div class="avatar-lg-placeholder">
                    {{ strtoupper(substr($pelajar->pengguna->fld_user_nama ?? 'P', 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="student-header-info">
            <h2>{{ $pelajar->pengguna->fld_user_nama ?? 'Tiada Nama' }}</h2>
            <div class="student-header-meta">
                <span><i class="fas fa-id-badge"></i> {{ $pelajar->fld_pel_nomat }}</span>
                <span><i class="fas fa-graduation-cap"></i> Tahun {{ $pelajar->fld_pel_tahun }}</span>
                <span><i class="fas fa-laptop-code"></i> {{ $pelajar->fld_pel_jurusan }}</span>
                <span><i class="fas fa-users"></i> {{ $sigNama }}</span>
            </div>
        </div>
        <!-- Overall Score Display -->
        <div class="overall-score" id="overallScore">
            <span class="score-value" id="overallScoreValue">0</span>
            <span class="score-label">/ 100</span>
        </div>
    </div>

    <!-- Criteria Cards -->
    <div class="kriteria-grid">
        @foreach($kriterias as $kriteria)
        <div class="kriteria-card" data-krit-id="{{ $kriteria->fld_krit_id }}" data-max="{{ $kriteria->fld_krit_markah }}">
            <div class="kriteria-card-header">
                <h3>{{ $kriteria->fld_krit_nama }}</h3>
                <span class="kriteria-badge">{{ $kriteria->fld_krit_markah }}%</span>
            </div>

            <!-- Kriteria Progress -->
            <div class="kriteria-progress">
                <div class="kriteria-progress-bar" id="krit-progress-{{ $kriteria->fld_krit_id }}"></div>
            </div>
            <div class="kriteria-progress-text" id="krit-progress-text-{{ $kriteria->fld_krit_id }}">0 / {{ $kriteria->fld_krit_markah }}%</div>

            <!-- Subkriteria List -->
            @forelse($kriteria->subkriteria as $sub)
            <div class="sub-section" data-sub-id="{{ $sub->fld_sub_id }}">
                <div class="sub-header">
                    <span class="sub-nama">{{ $sub->fld_sub_nama }}</span>
                    <span class="sub-score-badge" id="sub-score-{{ $sub->fld_sub_id }}">0 / {{ $sub->fld_sub_markah ?? 0 }}%</span>
                </div>

                @if(isset($assignmentScores[$sub->fld_sub_id]))
                    <div class="assignment-notice">
                        <div class="assignment-notice-content">
                            @if(isset($assignmentScores[$sub->fld_sub_id]['is_attendance']) && $assignmentScores[$sub->fld_sub_id]['is_attendance'])
                                <span class="assignment-notice-title">Markah Kehadiran: {{ $assignmentScores[$sub->fld_sub_id]['score'] }}% / 100%</span>
                            @else
                                <span class="assignment-notice-title">Markah Tugasan: {{ $assignmentScores[$sub->fld_sub_id]['score'] }} / 10</span>
                            @endif
                        </div>
                    </div>
                @elseif($sub->descriptions->count() > 0)
                <div class="desc-marking-list">
                    @foreach($sub->descriptions as $desc)
                    <div class="desc-marking-row">
                        <div class="desc-marking-text">
                            {{ $desc->fld_desc_text }}
                        </div>
                        <div class="desc-marking-stars">
                            <input type="hidden"
                                   class="desc-mark-input"
                                   data-krit-id="{{ $kriteria->fld_krit_id }}"
                                   data-sub-id="{{ $sub->fld_sub_id }}"
                                   data-desc-id="{{ $desc->fld_desc_id }}"
                                   data-max="{{ $desc->fld_desc_markah }}"
                                   data-sub-weight="{{ $sub->fld_sub_markah }}"
                                   data-krit-weight="{{ $kriteria->fld_krit_markah }}"
                                   value="{{ isset($existingMarks[$desc->fld_desc_id]) ? intval($existingMarks[$desc->fld_desc_id]) : 0 }}">
                            <div class="star-group" data-desc-id="{{ $desc->fld_desc_id }}">
                                @for($i = 1; $i <= 5; $i++)
                                <span class="star" data-value="{{ $i }}" onclick="rateStar(this, {{ $i }})" onmouseenter="hoverStar(this, {{ $i }})" onmouseleave="unhoverStar(this)">
                                    <i class="fas fa-star"></i>
                                </span>
                                @endfor
                            </div>
                            <span class="star-score-text" id="star-text-{{ $desc->fld_desc_id }}">{{ isset($existingMarks[$desc->fld_desc_id]) ? intval($existingMarks[$desc->fld_desc_id]) : 0 }} / 5</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="desc-empty">
                    <i class="fas fa-info-circle"></i> Tiada penerangan untuk subkriteria ini.
                </div>
                @endif
            </div>
            @empty
            <div class="sub-empty">
                <i class="fas fa-inbox"></i>
                <span>Tiada subkriteria ditetapkan.</span>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>

    <!-- Comment Section -->
    <div class="comment-section">
        <h3><i class="fas fa-comment-dots"></i> Ulasan / Komen Tambahan</h3>
        <textarea id="komenPenilaian" class="komen-textarea" placeholder="Sila masukkan komen atau ulasan tentang prestasi pelajar (pilihan)...">{{ $existingKomen ?? '' }}</textarea>
    </div>

    <!-- Bottom Save Bar -->
    <div class="save-bar">
        <div class="save-bar-info">
            <span class="save-bar-total">Jumlah Markah: <strong id="saveTotalScore">0</strong> / 100%</span>
            <span class="save-bar-grade" id="saveGrade"></span>
        </div>
        <button type="button" class="btn-save-markah" id="btnSaveMarkah" onclick="saveMarks()">
            <i class="fas fa-save"></i> Simpan Penilaian
        </button>
    </div>

    <!-- Save feedback toast -->
    <div id="saveToast" class="save-toast" style="display:none;"></div>
</div>

<script>
    // ============================
    // DATA FROM SERVER
    // ============================

    // Subkriteria metadata: { sub_id: { maxDescTotal, subWeight } }
    const subMeta = {};
    // Kriteria metadata: { krit_id: { kritWeight, subIds: [] } }
    const kritMeta = {};

    @foreach($kriterias as $kriteria)
        kritMeta['{{ $kriteria->fld_krit_id }}'] = {
            kritWeight: {{ $kriteria->fld_krit_markah }},
            subIds: []
        };
        @foreach($kriteria->subkriteria as $sub)
            kritMeta['{{ $kriteria->fld_krit_id }}'].subIds.push('{{ $sub->fld_sub_id }}');
            subMeta['{{ $sub->fld_sub_id }}'] = {
                subWeight: {{ $sub->fld_sub_markah ?? 0 }},
                maxDescTotal: {{ $sub->descriptions->sum('fld_desc_markah') }},
                kritId: '{{ $kriteria->fld_krit_id }}',
                isAssignment: {{ isset($assignmentScores[$sub->fld_sub_id]) && empty($assignmentScores[$sub->fld_sub_id]['is_attendance']) ? 'true' : 'false' }},
                isAttendance: {{ isset($assignmentScores[$sub->fld_sub_id]) && !empty($assignmentScores[$sub->fld_sub_id]['is_attendance']) ? 'true' : 'false' }},
                assignmentScore: {{ isset($assignmentScores[$sub->fld_sub_id]) ? floatval($assignmentScores[$sub->fld_sub_id]['score']) : 0 }}
            };
        @endforeach
    @endforeach

    // ============================
    // STAR RATING LOGIC
    // ============================

    function rateStar(starEl, value) {
        const group = starEl.closest('.star-group');
        const descId = group.getAttribute('data-desc-id');
        const hiddenInput = group.parentElement.querySelector('.desc-mark-input');
        const currentVal = parseInt(hiddenInput.value) || 0;

        // Toggle off if clicking same star (reset to 0)
        if (currentVal === value) {
            hiddenInput.value = 0;
            value = 0;
        } else {
            hiddenInput.value = value;
        }

        // Update star visuals
        updateStarDisplay(group, value);

        // Update score text
        const textEl = document.getElementById('star-text-' + descId);
        if (textEl) textEl.textContent = value + ' / 5';

        recalculateAll();
    }

    function hoverStar(starEl, value) {
        const group = starEl.closest('.star-group');
        const stars = group.querySelectorAll('.star');
        stars.forEach((s, i) => {
            s.classList.toggle('hover', i < value);
        });
    }

    function unhoverStar(starEl) {
        const group = starEl.closest('.star-group');
        const stars = group.querySelectorAll('.star');
        stars.forEach(s => s.classList.remove('hover'));
    }

    function updateStarDisplay(group, value) {
        const stars = group.querySelectorAll('.star');
        stars.forEach((s, i) => {
            s.classList.toggle('active', i < value);
        });
    }

    // Initialize all star displays on page load
    function initStars() {
        document.querySelectorAll('.star-group').forEach(group => {
            const hiddenInput = group.parentElement.querySelector('.desc-mark-input');
            const val = parseInt(hiddenInput.value) || 0;
            updateStarDisplay(group, val);
        });
    }

    /**
     * Recalculate all percentages:
     *
     * For each subkriteria:
     *   - Sum all desc marks entered (e.g., 3+4+5 = 12 out of 15 total desc max)
     *   - Sub percentage = (earned / maxDescTotal) * subWeight
     *     e.g., (12/15) * 5 = 4.0 (this is the sub's contribution in kriteria %)
     *
     * For each kriteria:
     *   - Sum all sub percentages = kriteria earned percentage
     *   - This is directly out of fld_krit_markah (e.g., 4.0 / 15%)
     *
     * Overall score:
     *   - Sum all kriteria earned percentages (out of 100)
     */
    function recalculateAll() {
        // Step 1: Gather all desc marks grouped by sub_id
        const subDescTotals = {};  // { sub_id: total_earned }
        document.querySelectorAll('.desc-mark-input').forEach(input => {
            const subId = input.getAttribute('data-sub-id');
            const val = parseFloat(input.value) || 0;
            subDescTotals[subId] = (subDescTotals[subId] || 0) + val;
        });

        // Step 2: Calculate sub percentages and update sub badges
        const subPercentages = {};  // { sub_id: earned_percentage }
        for (const [subId, meta] of Object.entries(subMeta)) {
            let subPct = 0;
            
            if (meta.isAttendance) {
                // Attendance is a percentage out of 100
                subPct = (meta.assignmentScore / 100) * meta.subWeight;
            } else if (meta.isAssignment) {
                // Assignments are marked out of 10
                subPct = (meta.assignmentScore / 10) * meta.subWeight;
            } else {
                const earned = subDescTotals[subId] || 0;
                if (meta.maxDescTotal > 0) {
                    // (earned / total_desc_max) * sub_weight_in_kriteria
                    subPct = (earned / meta.maxDescTotal) * meta.subWeight;
                }
            }
            subPercentages[subId] = subPct;

            // Update sub score badge - show actual earned % out of sub weight
            const badge = document.getElementById('sub-score-' + subId);
            if (badge) {
                badge.textContent = subPct.toFixed(2) + ' / ' + meta.subWeight + '%';

                // Color coding
                badge.classList.remove('full', 'over');
                if (subPct >= meta.subWeight) {
                    badge.classList.add('full');
                } else if (subPct > meta.subWeight) {
                    badge.classList.add('over');
                }
            }
        }

        // Step 3: Calculate kriteria totals
        let overallTotal = 0;

        for (const [kritId, meta] of Object.entries(kritMeta)) {
            let kriteriaEarned = 0;

            meta.subIds.forEach(subId => {
                kriteriaEarned += (subPercentages[subId] || 0);
            });

            // Update kriteria progress bar
            const bar = document.getElementById('krit-progress-' + kritId);
            const text = document.getElementById('krit-progress-text-' + kritId);

            if (bar && text) {
                let pct = meta.kritWeight > 0 ? (kriteriaEarned / meta.kritWeight) * 100 : 0;
                if (pct > 100) pct = 100;

                bar.style.width = pct + '%';

                // Color by percentage
                if (kriteriaEarned > meta.kritWeight) {
                    bar.style.backgroundColor = '#dc2626';
                    text.innerHTML = '<span style="color:#dc2626;font-weight:700;"><i class="fas fa-exclamation-triangle"></i> ' + kriteriaEarned.toFixed(2) + ' / ' + meta.kritWeight + '%</span>';
                } else if (pct >= 100) {
                    bar.style.backgroundColor = '#10b981';
                    text.textContent = kriteriaEarned.toFixed(2) + ' / ' + meta.kritWeight + '%';
                } else if (pct >= 50) {
                    bar.style.backgroundColor = '#f59e0b';
                    text.textContent = kriteriaEarned.toFixed(2) + ' / ' + meta.kritWeight + '%';
                } else {
                    bar.style.backgroundColor = '#ef4444';
                    text.textContent = kriteriaEarned.toFixed(2) + ' / ' + meta.kritWeight + '%';
                }
            }

            overallTotal += kriteriaEarned;
        }

        // Step 4: Update overall score
        const roundedTotal = Math.round(overallTotal * 100) / 100;
        document.getElementById('overallScoreValue').textContent = roundedTotal.toFixed(2);
        document.getElementById('saveTotalScore').textContent = roundedTotal.toFixed(2);

        // Update grade display
        const grade = calculateGrade(roundedTotal);
        const gradeEl = document.getElementById('saveGrade');
        if (gradeEl) {
            gradeEl.textContent = 'Gred: ' + grade;
            gradeEl.style.color = getGradeColor(grade);
        }

        // Overall score color
        const scoreEl = document.querySelector('.overall-score');
        scoreEl.classList.remove('score-low', 'score-mid', 'score-high');
        if (roundedTotal >= 80) {
            scoreEl.classList.add('score-high');
        } else if (roundedTotal >= 50) {
            scoreEl.classList.add('score-mid');
        } else {
            scoreEl.classList.add('score-low');
        }
    }

    function calculateGrade(score) {
        if (score >= 90) return 'A+';
        if (score >= 80) return 'A';
        if (score >= 75) return 'A-';
        if (score >= 70) return 'B+';
        if (score >= 65) return 'B';
        if (score >= 60) return 'B-';
        if (score >= 55) return 'C+';
        if (score >= 50) return 'C';
        if (score >= 45) return 'C-';
        if (score >= 40) return 'D';
        return 'F';
    }

    function getGradeColor(grade) {
        if (grade.startsWith('A')) return '#059669';
        if (grade.startsWith('B')) return '#2563eb';
        if (grade.startsWith('C')) return '#d97706';
        if (grade === 'D') return '#ea580c';
        return '#dc2626';
    }

    // ============================
    // SAVE MARKS VIA AJAX
    // ============================
    function saveMarks() {
        const btn = document.getElementById('btnSaveMarkah');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        // Collect all desc marks
        const marks = [];
        document.querySelectorAll('.desc-mark-input').forEach(input => {
            marks.push({
                krit_id: input.getAttribute('data-krit-id'),
                sub_id: input.getAttribute('data-sub-id'),
                desc_id: input.getAttribute('data-desc-id'),
                markah: parseFloat(input.value) || 0,
            });
        });

        // Collect comment
        const komen = document.getElementById('komenPenilaian').value;

        fetch('{{ route("penilaian.simpan", $pelajar->fld_pel_nomat) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ marks: marks, komen: komen })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message + ' (' + data.overallScore + '% - Gred ' + data.grade + ')');
                // Redirect back to the penilaian list page after a short delay
                setTimeout(() => {
                    window.location.href = '{{ route("penilaian") }}';
                }, 1500);
            } else {
                showToast('error', data.message || 'Ralat berlaku.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Penilaian';
            }
        })
        .catch(err => {
            showToast('error', 'Ralat rangkaian. Sila cuba lagi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Simpan Penilaian';
        });
    }

    function showToast(type, message) {
        const toast = document.getElementById('saveToast');
        toast.className = 'save-toast ' + (type === 'success' ? 'toast-success' : 'toast-error');
        toast.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
        toast.style.display = 'flex';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 4000);
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initStars();
        recalculateAll();
    });

</script>
@endsection
