<link rel="stylesheet" href="{{ asset('css/styletopbar.css') }}">
<div class="topbar">
    <div class="topbar-left">
        <h2 class="page-title">
            @php
                $pageTitle = View::getSections()['title'] ?? '';
                // Membuang '- ' di pangkal tajuk jika ada
                $pageTitle = trim(str_replace('- ', '', $pageTitle));
                
                // Menukar 'Dashboard' kepada 'Papan Pemuka' sekiranya page dashboard
                if (strtolower($pageTitle) == 'dashboard') {
                    $pageTitle = 'Papan Pemuka';
                }
                
                echo $pageTitle ?: 'Papan Pemuka'; // Fallback text
            @endphp
        </h2>
    </div>
    <div class="topbar-right">
        <div class="user-profile">
            <span class="user-name">{{ Auth::check() ? Auth::user()->fld_user_nama : 'Pengguna' }}</span>
            @if(Auth::check() && Auth::user()->fld_user_role == 1)
                <div class="user-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
            @endif
            @if(Auth::check() && Auth::user()->fld_user_role == 2)
                <div class="user-avatar">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            @endif
            @if(Auth::check() && Auth::user()->fld_user_role == 3)
                <div class="user-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
            @endif
            @if(Auth::check() && Auth::user()->fld_user_role == 4)
                <div class="user-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
            @endif
            
        </div>
    </div>
</div>
