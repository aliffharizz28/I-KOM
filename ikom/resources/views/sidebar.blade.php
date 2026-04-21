<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sidebar</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/stylesidebar.css') }}">
    </head>
    <body>
        <div class="sidebar">

            <div class="logo-container">
                <img src="{{ asset('pic/logoikomputih.png') }}" alt="logo I-KOM" class="logo">   
                <h3>Sistem Penilaian dan Pengurusan Kursus <br>Inovasi Digital & Komuniti Digital</h3> 
            </div>

            <hr>

            <div class="nav-container">

                @if(Auth::check() && Auth::user()->fld_user_role == 1)
                    <a href="{{ route('dashboard') }}" class="pm"><i class="fa fa-tachometer"></i> Papan Pemuka </a>
                    <a href="{{ route('coursereg') }}" class="pm"><i class="fa fa-plus-square"></i> Daftar Kursus </a>
                    <a href="{{ route('penyelarasSigRegistration') }}" class="pm"><i class="fa fa-id-card"></i>Daftar Penyelaras SIG </a>
                @endif

                @if(Auth::check() && Auth::user()->fld_user_role == 2)
                    <a href="{{ route('dashboard') }}" class="pm"><i class="fa fa-tachometer"></i> Papan Pemuka </a>
                    <a href="{{ route('registration') }}" class="pm"><i class="fa fa-users"></i>  Daftar Pelajar </a>
                    <a href="#" class="pm"><i class="fa fa-pencil-square"></i> Sub-Kriteria Markah </a>
                    <a href="{{ route('tugasan') }}" class="pm"><i class="fas fa-tasks"></i> Tugasan </a>
                    <a href="#" class="pm"><i class="fas fa-chart-line"></i> Penilaian Markah </a>
                    <a href="#" class="pm"><i class="fas fa-check-circle"></i> Kehadiran </a>
                @endif
                
                @if(Auth::check() && Auth::user()->fld_user_role == 3)
                    <a href="{{ route('dashboard') }}" class="pm"><i class="fa fa-tachometer"></i> Papan Pemuka Pelajar </a>
                    <a href="{{ route('tugasanPelajar') }}" class="pm"><i class="fas fa-tasks"></i> Tugasan </a>
                @endif
                
                @if(Auth::check() && Auth::user()->fld_user_role == 4)
                    <a href="{{ route('dashboard') }}" class="pm"><i class="fa fa-tachometer"></i> Papan Pemuka MT </a>
                @endif
            </div>


            <div class="logout-container">
                <hr>
               <form action="{{ route('logout') }}" method="POST" id="logout-form">
               @csrf
                <a href="#" class="logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out-alt"></i> Log Keluar
                </a>
               </form>
            </div>

        </div>
    </body>