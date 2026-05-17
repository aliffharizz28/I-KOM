<div class="sidebar" id="mainSidebar">

    {{-- Toggle button to collapse sidebar (chevron left icon) --}}
    <button id="closeSidebarBtn" class="sidebar-toggle-btn" title="Minimize Sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>

    <a href="{{ route('dashboard') }}" class="logo-container" style="text-decoration: none;">
        <img src="{{ asset('pic/logoikomputih.png') }}" alt="logo I-KOM" class="logo">   
        <h3>Sistem Penilaian dan Pengurusan Kursus <br>Inovasi Digital &amp; Komuniti Digital</h3> 
    </a>

    <div class="nav-container">

        @if(Auth::check() && Auth::user()->fld_user_role == 1)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka</span></a>
            <a href="{{ route('sesiKursus.index') }}" class="nav-item {{ request()->routeIs('sesiKursus.*') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> <span>Tetapan Sesi Kursus</span></a>
            <a href="{{ route('penyelarasSigRegistration') }}" class="nav-item {{ request()->routeIs('penyelarasSigRegistration') ? 'active' : '' }}"><i class="fa fa-id-card"></i> <span>Pendaftaran Penyelaras SIG</span></a>
            <a href="{{ route('laporanSIG') }}" class="nav-item {{ request()->routeIs('laporanSIG') ? 'active' : '' }}"><i class="fas fa-file-excel"></i> <span>Laporan SIG</span></a>
        @endif

        @if(Auth::check() && Auth::user()->fld_user_role == 2) 
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka</span></a>
            <a href="{{ route('registration') }}" class="nav-item {{ request()->routeIs('registration') ? 'active' : '' }}"><i class="fa fa-users"></i> <span>Pendaftaran Pelajar</span></a>
            <a href="{{ route('subkriteria') }}" class="nav-item {{ request()->routeIs('subkriteria') ? 'active' : '' }}"><i class="fa fa-pencil-square"></i> <span>Rubrik Pemarkahan</span></a>
            <a href="{{ route('tugasan') }}" class="nav-item {{ request()->routeIs('tugasan') ? 'active' : '' }}"><i class="fas fa-tasks"></i> <span>Tugasan</span></a>
            <a href="{{ route('penilaian') }}" class="nav-item {{ request()->routeIs('penilaian') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> <span>Penilaian Markah</span></a>
            <a href="{{ route('kehadiran') }}" class="nav-item {{ request()->routeIs('kehadiran') ? 'active' : '' }}"><i class="fas fa-check-circle"></i> <span>Semakan Kehadiran</span></a>
        @endif
        
        @if(Auth::check() && Auth::user()->fld_user_role == 3) 
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer"></i> <span>Papan Pemuka</span></a>
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

{{-- Floating button shown only when sidebar is hidden --}}
<button id="openSidebarBtn" class="sidebar-open-fab" title="Open Sidebar">
    <i class="fas fa-bars"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar   = document.getElementById('mainSidebar');
        const openBtn   = document.getElementById('openSidebarBtn');
        const closeBtn  = document.getElementById('closeSidebarBtn');
        const contentArea = document.querySelector('.content-area');

        function collapseSidebar() {
            sidebar.classList.add('collapsed');
            if (contentArea) contentArea.classList.add('sidebar-hidden');
            // Show open FAB after transition ends
            setTimeout(() => openBtn.classList.add('visible'), 280);
            localStorage.setItem('sidebarState', 'collapsed');
        }

        function expandSidebar() {
            sidebar.classList.remove('collapsed');
            if (contentArea) contentArea.classList.remove('sidebar-hidden');
            openBtn.classList.remove('visible');
            localStorage.setItem('sidebarState', 'open');
        }

        // Restore state from localStorage
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            // Apply instantly (no animation on page load)
            sidebar.classList.add('collapsed', 'no-transition');
            if (contentArea) contentArea.classList.add('sidebar-hidden', 'no-transition');
            openBtn.classList.add('visible');
            // Re-enable transitions after paint
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    sidebar.classList.remove('no-transition');
                    if (contentArea) contentArea.classList.remove('no-transition');
                });
            });
        }

        closeBtn.addEventListener('click', collapseSidebar);
        openBtn.addEventListener('click', expandSidebar);
    });
</script>