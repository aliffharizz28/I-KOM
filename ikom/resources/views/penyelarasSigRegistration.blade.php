@extends('layouts.appikom')

@section('title', '- Pendaftaran Penyelaras SIG')

@section('content')
<!-- Import Google Fonts and FontAwesome -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- External CSS for Penyelaras SIG -->
<link rel="stylesheet" href="{{ asset('css/stylepenyelarasSig.css') }}?v={{ filemtime(public_path('css/stylepenyelarasSig.css')) }}">

<div class="sig-dashboard">

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

    <!-- SIG Grid -->
    <div class="sig-grid">
        
        <!-- Setup Logo Maps for SIGs -->
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
            $namaPenyelarasText = "No Penyelaras SIG Yet";
            $hasPenyelaras = false;
            
            $pid = '';
            $pname = '';
            $pemail = '';

            if ($currentPenyelaras && $currentPenyelaras->pengguna) {
                $pid = $currentPenyelaras->pengguna->fld_user_id;
                $pname = $currentPenyelaras->pengguna->fld_user_nama;
                $pemail = $currentPenyelaras->pengguna->fld_user_email;
                $namaPenyelarasText = $pname;
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
            
            <div class="card-actions">
                <button type="button" class="btn-action btn-add" title="Tambah Penyelaras" onclick="openModal('add', '{{ $sigId }}', '{{ addslashes($sigName) }}', '', '', '')">
                    <i class="fas fa-plus"></i>
                </button>
                @if($hasPenyelaras)
                <button type="button" class="btn-action btn-edit" title="Kemaskini Penyelaras" onclick="openModal('edit', '{{ $sigId }}', '{{ addslashes($sigName) }}', '{{ addslashes($pid) }}', '{{ addslashes($pname) }}', '{{ addslashes($pemail) }}')">
                    <i class="fas fa-pen"></i>
                </button>
                <button type="button" class="btn-action btn-delete" title="Padam Penyelaras" onclick="deleteSig('{{ addslashes($sigName) }}', '{{ addslashes($pid) }}')">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
        </div>
        @endforeach

    </div>
</div>

<!-- Form Modal for Add/Edit -->
<div class="modal-overlay" id="formModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Penyelaras</h3>
            <button class="btn-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        
        <p id="modalSubtitle" class="modal-subtitle">
            Sila masukkan maklumat penyelaras untuk <strong id="sigContextName"></strong>.
        </p>

        <form id="penyelarasForm" class="elegant-form">
            <!-- Hidden fields -->
            <input type="hidden" id="action_type" name="action_type" value="">
            <input type="hidden" id="sig_id" name="sig_id" value="">
            <input type="hidden" id="sig_name" name="sig_name" value="">

            <div class="form-group">
                <label for="penyelaras_id">Pilih Penyelaras SIG</label>
                <div class="input-wrapper">
                    <select id="penyelaras_id" name="penyelaras_id">
                        <option value="">Sila Pilih Penyelaras</option>
                        @foreach($availablePenyelaras as $user)
                            <option value="{{ $user->fld_user_id }}">{{ $user->fld_user_nama }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-user-tie icon"></i>
                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
            </div>

            <button type="button" class="btn-submit" onclick="submitForm()">
                <span id="btnSubmitText">Simpan Maklumat</span>
                <i class="fas fa-check-circle"></i>
            </button>
        </form>
    </div>
</div>

<!-- SweetAlert API can be optionally included in your master layout, using basic JS alert for now -->
<!-- JS Logic -->
<script>
    // Define global configuration required by the external script
    const csrfToken = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/penyelarasSig.js') }}?v={{ filemtime(public_path('js/penyelarasSig.js')) }}"></script>
@endsection