@extends('layouts.appikom')

@section('title', '- Laporan SIG')

@section('content')
<!-- Import Google Fonts and FontAwesome -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Reuse the CSS from Penyelaras SIG -->
<link rel="stylesheet" href="{{ asset('css/stylepenyelarasSig.css') }}?v={{ filemtime(public_path('css/stylepenyelarasSig.css')) }}">
<link rel="stylesheet" href="{{ asset('css/stylelaporanSIG.css') }}?v={{ file_exists(public_path('css/stylelaporanSIG.css')) ? filemtime(public_path('css/stylelaporanSIG.css')) : '' }}">

<div class="sig-dashboard">
    <!-- SIG Grid -->
    <div class="sig-grid">
        
        @php 
            $logoMaps = [
                'Intelligence Machines Club' => 'imachine.png',
                'CyberHack & Ethic' => 'cyber.png',
                'Inovasi Bisnes' => 'ibisnes.png',
                'Interactive Multimedia Club' => 'imec.png',
                'Mobile Application Development Club' => 'mad.png',
                'Autonomous Robot and Vision Systems' => 'arvis.png',
                'Programming Club' => 'pc.png',
                'Video Innovation Club' => 'vic.png',
            ];
        @endphp

        @foreach($dbSigs as $sig)
        @php
            $sigId = $sig->fld_sig_id;
            $sigName = $sig->fld_sig_nama;
            
            // Map the logos natively by name
            $logoFile = $logoMaps[$sigName] ?? 'imachine.png';
            
            // Check if there is an existing Penyelaras SIG assigned
            $currentPenyelaras = $sig->penyelarassig->first();
            $namaPenyelarasText = "Tiada Penyelaras SIG";
            $hasPenyelaras = false;
            
            if ($currentPenyelaras && $currentPenyelaras->pengguna) {
                $namaPenyelarasText = $currentPenyelaras->pengguna->fld_user_nama;
                $hasPenyelaras = true;
            }
        @endphp
        <div class="sig-card">
            <div class="card-image-wrapper">
                <img src="{{ asset('pic/logoSIG/' . $logoFile) }}" alt="{{ $sigName }} Logo" class="sig-logo">
            </div>
            
            <div class="sig-info">
                <div class="sig-title">
                    {{ $sigName }}
                </div>
                
                <div class="sig-status {{ $hasPenyelaras ? 'has-penyelaras' : 'no-penyelaras' }}">
                    <i class="fas {{ $hasPenyelaras ? 'fa-user-check' : 'fa-user-times' }}"></i>
                    {{ $namaPenyelarasText }}
                </div>
            </div>
            
            <div class="card-actions laporan-actions">
                <a href="{{ route('laporanSIG.view', $sigId) }}" class="btn-laporan-view" title="Papar Laporan">
                    <i class="fas fa-eye"></i> 
                </a>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
