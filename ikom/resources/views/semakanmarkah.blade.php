@extends('layouts.appikom')

@section('title', '- Semakan Markah')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylesemakanmarkah.css') }}?v={{ filemtime(public_path('css/stylesemakanmarkah.css')) }}">

<div class="semakan-wrapper">
    
    @if($publishStatus == 0 || !$keputusan)
        <div class="status-banner">
            <i class="fas fa-info-circle"></i>
            <div>Markah anda belum dinilai atau belum diterbitkan. Sila semak semula nanti.</div>
        </div>
    @else
        @php
            $isPhase1 = ($publishStatus == 1);
            $score = $isPhase1 ? $scorePhase1 : $keputusan->fld_total_markah;
            $maxScore = $isPhase1 ? 60 : 100;

            $scoreClass = 'score-mid';
            $percent = ($score / $maxScore) * 100;
            if($percent >= 80) $scoreClass = 'score-high';
            else if($percent < 50) $scoreClass = 'score-low';
        @endphp

        <!-- Top Row: Score and Comment -->
        <div class="top-row">
            <!-- Overall Score -->
            <div class="overall-card">
                <h3>Markah {{ $isPhase1 ? 'Penilaian Berterusan' : 'Keseluruhan' }}</h3>
                <div class="overall-score {{ $scoreClass }}">
                    <span class="score-value">{{ rtrim(rtrim(number_format($score, 2), '0'), '.') }}<span class="score-max">/{{ $maxScore }}</span></span>
                    @if(!$isPhase1)
                        <span class="score-label">Gred: {{ $keputusan->fld_nilai_gred ?? '-' }}</span>
                    @endif
                </div>
            </div>

            <!-- Comment Section -->
            <div class="comment-card">
                <h3 class="section-title"><i class="fas fa-comment-dots"></i> Ulasan Penilai</h3>
                <div class="comment-box">
                    @if(!empty($keputusan->fld_nilai_komen))
                        {{ $keputusan->fld_nilai_komen }}
                    @else
                        <div class="comment-empty">Tiada ulasan diberikan.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        @if($penilaians && count($penilaians) > 0)
            <div class="comment-section">
                <h3 class="section-title"><i class="fas fa-list-ul"></i> Pecahan Markah</h3>
                
                <div class="details-grid">
                    @foreach($penilaians as $penilaian)
                        @php
                            $kritName = $penilaian->kriteria->fld_krit_nama ?? 'Kriteria';
                            $showThis = true;
                            if ($isPhase1) {
                                $phase1KritList = ['idea inovasi & pemikiran kritis', 'perancangan projek', 'perlaksanaan projek', 'kerjasama kolaboratif'];
                                if (!in_array(strtolower(trim($kritName)), $phase1KritList)) {
                                    $showThis = false;
                                }
                            }
                        @endphp
                        
                        @if($showThis)
                        <div class="detail-card">
                            <div class="detail-kriteria-nama">
                                {{ $kritName }}
                            </div>
                            <div class="detail-mark">
                                <span class="detail-mark-label">Markah Diperolehi</span>
                                <span class="detail-mark-value">{{ rtrim(rtrim(number_format($penilaian->fld_nilai_markah, 2), '0'), '.') }}</span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        
    @endif

</div>
@endsection
