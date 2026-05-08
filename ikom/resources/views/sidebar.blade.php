<div class="sidebar">

    <a href="{{ route('dashboard') }}" class="logo-container" style="text-decoration: none;">
        <img src="{{ asset('pic/logoikomputih.png') }}" alt="logo I-KOM" class="logo">   
        <h3>Sistem Penilaian dan Pengurusan Kursus <br>Inovasi Digital & Komuniti Digital</h3> 
    </a>

    <div class="nav-container">

        @if(Auth::check() && Auth::user()->fld_user_role == 1)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka</span></a>
            <a href="{{ route('coursereg') }}" class="nav-item {{ request()->routeIs('coursereg') ? 'active' : '' }}"><i class="fa fa-plus-square"></i> <span>Daftar Kursus</span></a>
            <a href="{{ route('penyelarasSigRegistration') }}" class="nav-item {{ request()->routeIs('penyelarasSigRegistration') ? 'active' : '' }}"><i class="fa fa-id-card"></i> <span>Daftar Penyelaras SIG</span></a>
            <a href="{{ route('laporanSIG') }}" class="nav-item {{ request()->routeIs('laporanSIG') ? 'active' : '' }}"><i class="fas fa-file-excel"></i> <span>Laporan SIG</span></a>
        @endif

        @if(Auth::check() && Auth::user()->fld_user_role == 2)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka</span></a>
            <a href="{{ route('registration') }}" class="nav-item {{ request()->routeIs('registration') ? 'active' : '' }}"><i class="fa fa-users"></i> <span>Pendaftaran Pelajar</span></a>
            <a href="{{ route('subkriteria') }}" class="nav-item {{ request()->routeIs('subkriteria') ? 'active' : '' }}"><i class="fa fa-pencil-square"></i> <span>Rubrik Pemarkahan</span></a>
            <a href="{{ route('tugasan') }}" class="nav-item {{ request()->routeIs('tugasan') ? 'active' : '' }}"><i class="fas fa-tasks"></i> <span>Tugasan</span></a>
            <a href="{{ route('penilaian') }}" class="nav-item {{ request()->routeIs('penilaian') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> <span>Penilaian Markah</span></a>
            <a href="{{ route('kehadiran') }}" class="nav-item {{ request()->routeIs('kehadiran') ? 'active' : '' }}"><i class="fas fa-check-circle"></i> <span>Kehadiran</span></a>
        @endif
        
        @if(Auth::check() && Auth::user()->fld_user_role == 3)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka Pelajar</span></a>
            <a href="{{ route('tugasanPelajar') }}" class="nav-item {{ request()->routeIs('tugasanPelajar') ? 'active' : '' }}"><i class="fas fa-tasks"></i> <span>Tugasan</span></a>
            <a href="{{ route('semakanmarkah') }}" class="nav-item {{ request()->routeIs('semakanmarkah') ? 'active' : '' }}"><i class="fas fa-graduation-cap"></i> <span>Semakan Markah</span></a>
            @if(Auth::user()->pelajar && Auth::user()->pelajar->fld_pel_mt == 1)
                <a href="{{ route('kehadiran') }}" class="nav-item {{ request()->routeIs('kehadiran') ? 'active' : '' }}"><i class="fas fa-check-circle"></i> <span>Kehadiran</span></a>
            @endif
        @endif
        
    </div>

    <div class="logout-container">
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
        @csrf
        <a href="#" class="nav-item logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa fa-sign-out-alt"></i> <span>Log Keluar</span>
        </a>
        </form>
    </div>

</div>