@extends('layouts.appikom')

@section('title', '- Rekod Kehadiran')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylekehadiran.css') }}?v={{ file_exists(public_path('css/stylekehadiran.css')) ? filemtime(public_path('css/stylekehadiran.css')) : '' }}">

<div class="kehadiran-wrapper">
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="list-header">
        <h2><i class="fas fa-clipboard-check"></i> Rekod Kehadiran: {{ $perjumpaan->fld_meet_topik }}</h2>
        <a href="{{ route('kehadiran') }}" class="btn-reset">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-container section-container table-container">
        <p><strong>Tarikh:</strong> {{ \Carbon\Carbon::parse($perjumpaan->fld_meet_tarikh)->format('d M Y') }}</p>
        <p><strong>Status Pengesahan:</strong> {!! $perjumpaan->fld_meet_verify ? '<span style="color:green;">Telah Disahkan</span>' : '<span style="color:red;">Belum Disahkan</span>' !!}</p>
        
        @if(Auth::user()->fld_user_role == 3 && $perjumpaan->fld_meet_verify == 0)
        <form action="{{ route('kehadiran.simpan', $perjumpaan->fld_meet_id) }}" method="POST">
            @csrf
        @endif
            <table class="custom-table mt-4" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>No. Matrik</th>
                        <th>Nama Pelajar</th>
                        <th>Peratusan Keseluruhan (Sahkan)</th>
                        <th>Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pelajars as $pelajar)
                        @php
                            $rekodSediaAda = $pelajar->kehadiran->first();
                            $status = $rekodSediaAda ? $rekodSediaAda->fld_hdr_status : 'Tidak Hadir';
                        @endphp
                        <tr>
                            <td>{{ $pelajar->fld_pel_nomat }}</td>
                            <td>{{ $pelajar->pengguna->fld_user_nama ?? '-' }}</td>
                            <td>
                                @php
                                    $peratusan = $pelajar->peratusan_kehadiran;
                                    $color = $peratusan >= 80 ? 'green' : ($peratusan >= 50 ? 'orange' : 'red');
                                @endphp
                                <strong style="color: {{ $color }};">{{ $peratusan }}%</strong>
                            </td>
                            <td>
                                @if(Auth::user()->fld_user_role == 3 && $perjumpaan->fld_meet_verify == 0)
                                    <select name="kehadiran[{{ $pelajar->fld_pel_nomat }}]" class="form-control" style="width: auto;">
                                        <option value="Hadir" {{ $status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="Tidak Hadir" {{ $status == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                    </select>
                                @else
                                    @if($status == 'Hadir')
                                        <span class="status-badge" style="background:#c6f6d5;color:#2f855a;padding:5px 10px;border-radius:15px;font-size:12px;">Hadir</span>
                                    @else
                                        <span class="status-badge" style="background:#fed7d7;color:#c53030;padding:5px 10px;border-radius:15px;font-size:12px;">Tidak Hadir</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @if(Auth::user()->fld_user_role == 3 && $perjumpaan->fld_meet_verify == 0)
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Simpan Kehadiran</button>
            </div>
        </form>
        @endif

        @if(Auth::user()->fld_user_role == 2 && $perjumpaan->fld_meet_verify == 0)
        <form action="{{ route('kehadiran.sahkan', $perjumpaan->fld_meet_id) }}" method="POST" style="margin-top: 20px;" onsubmit="return confirm('Adakah anda pasti untuk mengesahkan kehadiran perjumpaan ini?');">
            @csrf
            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);"><i class="fas fa-check-double"></i> Sahkan Kehadiran</button>
        </form>
        @endif
    </div>
</div>
@endsection
