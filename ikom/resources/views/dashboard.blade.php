@extends('layouts.appikom')

@section('title', '- Papan Pemuka')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styledashboard.css') }}?v={{ filemtime(public_path('css/styledashboard.css')) }}">

@if (Auth::user()->fld_user_role == 1)
<div class="dashboard-wrapper">
    <div class="info-container">
        <div class="info-box box-pelajar">
            <div class="box-icon">
                <i class="fas fa-user"></i> </div>
            <div class="box-data">
                <p class="big-number">20</p> <h3>Jumlah Pelajar Perlu Dinilai</h3>
            </div>
        </div>

        <div class="info-box box-kehadiran">
            <div class="box-icon">
                <i class="fas fa-user-check"></i> </div>
            <div class="box-data">
                <p class="big-number percent-green">80%</p> <h3>Peratusan Kehadiran Pelajar</h3> </div>
        </div>

        <div class="info-box box-lewat">
            <div class="box-icon">
                <i class="fas fa-exclamation-triangle"></i> </div>
            <div class="box-data">
                <p class="big-number warning-red">7</p> <h3>Lewat Hantar Tugasan</h3> </div>
        </div>
    </div>

    <div class="dashboard-content-row">
        <div class="infoterkini-container">
            <div class="card-header">
                <h2>Informasi Terkini</h2> </div>
            <div class="card-body">
                <table class="table-terkini">
                    <tbody>
                        <tr>
                            <td>Penilaian Tugas 1</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td> 
                        </tr>
                        <tr>
                            <td>Laporan Kehadiran Minggu 3</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Tugas 2</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Markah PB</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="garismasa-container">
            <div class="card-header">
                <h2>Garis Masa Kursus Inovasi Digital</h2> </div>
            <div class="card-body timelines">
                <div class="timeline-item">
                    <h3>Minggu 1:</h3>
                    <p>Pengenalan kepada Kursus Inovasi Digital</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 2-3:</h3>
                    <p>Brainstorming Idea dan Tujuan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 4:</h3>
                    <p>Perancangan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 5:</h3>
                    <p>Pembentukan Jawatankuasa Program</p> </div>
            </div>
        </div>
    </div>
</div>
@endif

@if (Auth::user()->fld_user_role == 2)
<div class="dashboard-wrapper">
    <div class="info-container">
        <div class="info-box box-pelajar">
            <div class="box-icon">
                <i class="fas fa-user"></i> </div>
            <div class="box-data">
                <p class="big-number">20</p> <h3>Jumlah Pelajar Perlu Dinilai</h3>
            </div>
        </div>

        <div class="info-box box-kehadiran">
            <div class="box-icon">
                <i class="fas fa-user-check"></i> </div>
            <div class="box-data">
                <p class="big-number percent-green">80%</p> <h3>Peratusan Kehadiran Pelajar</h3> </div>
        </div>

        <div class="info-box box-lewat">
            <div class="box-icon">
                <i class="fas fa-exclamation-triangle"></i> </div>
            <div class="box-data">
                <p class="big-number warning-red">7</p> <h3>Lewat Hantar Tugasan</h3> </div>
        </div>
    </div>

    <div class="dashboard-content-row">
        <div class="infoterkini-container">
            <div class="card-header">
                <h2>Informasi Terkini</h2> </div>
            <div class="card-body">
                <table class="table-terkini">
                    <tbody>
                        <tr>
                            <td>Penilaian Tugas 1</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td> 
                        </tr>
                        <tr>
                            <td>Laporan Kehadiran Minggu 3</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Tugas 2</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Markah PB</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="garismasa-container">
            <div class="card-header">
                <h2>Garis Masa Kursus Inovasi Digital</h2> </div>
            <div class="card-body timelines">
                <div class="timeline-item">
                    <h3>Minggu 1:</h3>
                    <p>Pengenalan kepada Kursus Inovasi Digital</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 2-3:</h3>
                    <p>Brainstorming Idea dan Tujuan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 4:</h3>
                    <p>Perancangan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 5:</h3>
                    <p>Pembentukan Jawatankuasa Program</p> </div>
            </div>
        </div>
    </div>
</div>
@endif

@if (Auth::user()->fld_user_role == 3)
<div class="dashboard-wrapper">
    <div class="info-container">
        <div class="info-box box-pelajar">
            <div class="box-icon">
                <i class="fas fa-user"></i> </div>
            <div class="box-data">
                <p class="big-number">20</p> <h3>Jumlah Pelajar Perlu Dinilai</h3>
            </div>
        </div>

        <div class="info-box box-kehadiran">
            <div class="box-icon">
                <i class="fas fa-user-check"></i> </div>
            <div class="box-data">
                <p class="big-number percent-green">80%</p> <h3>Peratusan Kehadiran Pelajar</h3> </div>
        </div>

        <div class="info-box box-lewat">
            <div class="box-icon">
                <i class="fas fa-exclamation-triangle"></i> </div>
            <div class="box-data">
                <p class="big-number warning-red">7</p> <h3>Lewat Hantar Tugasan</h3> </div>
        </div>
    </div>

    <div class="dashboard-content-row">
        <div class="infoterkini-container">
            <div class="card-header">
                <h2>Informasi Terkini</h2> </div>
            <div class="card-body">
                <table class="table-terkini">
                    <tbody>
                        <tr>
                            <td>Penilaian Tugas 1</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td> 
                        </tr>
                        <tr>
                            <td>Laporan Kehadiran Minggu 3</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Tugas 2</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                        <tr>
                            <td>Penilaian Markah PB</td>
                            <td class="text-right"><a href="#" class="btn-mulai"><i class="fas fa-chevron-right"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="garismasa-container">
            <div class="card-header">
                <h2>Garis Masa Kursus Inovasi Digital</h2> </div>
            <div class="card-body timelines">
                <div class="timeline-item">
                    <h3>Minggu 1:</h3>
                    <p>Pengenalan kepada Kursus Inovasi Digital</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 2-3:</h3>
                    <p>Brainstorming Idea dan Tujuan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 4:</h3>
                    <p>Perancangan Program</p> </div>
                <div class="timeline-item">
                    <h3>Minggu 5:</h3>
                    <p>Pembentukan Jawatankuasa Program</p> </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection