<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk | I-KOM</title>
    <link rel="icon" type="image/png" href="{{ asset('pic/logoikomputih.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stylelogin.css') }}?v={{ filemtime(public_path('css/stylelogin.css')) }}">
</head>
<body>
    {{-- Compact header shown only on mobile (≤768px) --}}
    <div class="mobile-header">
        <div class="mobile-uni-logos">
            <img src="{{ asset('pic/LogoRasmiUKM.png') }}" alt="UKM">
            <img src="{{ asset('pic/ftsm-black.png') }}" alt="FTSM">
        </div>
        <div class="mobile-divider"></div>
        <img src="{{ asset('pic/logoIKOMblack-removebg-preview.png') }}" alt="I-KOM" class="mobile-main-logo">
    </div>

    <div class="split-layout">
        <!-- Left Side: Branding (Desktop & Tablet only) -->
        <div class="left-side">
            <div class="branding-content">
                <div class="university-logos">
                    <img src="{{ asset('pic/LogoRasmiUKM.png') }}" alt="UKM" class="uni-logo">
                    <img src="{{ asset('pic/ftsm-black.png') }}" alt="FTSM" class="uni-logo">
                </div>
                <img src="{{ asset('pic/logoIKOMblack-removebg-preview.png') }}" alt="I-KOM Logo" class="main-logo">
                <p class="tagline">Sistem Penilaian dan Pengurusan Kursus Inovasi Digital & Komuniti Digital</p>
                
                <!-- SIG Logos Carousel -->
                <div class="sig-carousel-section">
                    <p class="carousel-title">Special Interest Groups</p>
                    <div class="carousel-container">
                        <div class="carousel-track">
                            @php
                                $sigLogos = [
                                    'imachine.png', 'cyber.png', 'ibisnes.png', 'imec.png',
                                    'mad.png', 'arvis.png', 'pc.png', 'vic.png'
                                ];
                            @endphp
                            
                            {{-- First set of logos --}}
                            @foreach($sigLogos as $logo)
                                <div class="carousel-item">
                                    <img src="{{ asset('pic/logoSIG/' . $logo) }}" alt="SIG Logo">
                                </div>
                            @endforeach
                            
                            {{-- Duplicate set for seamless loop --}}
                            @foreach($sigLogos as $logo)
                                <div class="carousel-item">
                                    <img src="{{ asset('pic/logoSIG/' . $logo) }}" alt="SIG Logo">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side: Login Form -->
        <div class="right-side">
            <div class="login-card">
                <div class="login-header">
                    <h1>Log Masuk</h1>
                    <p>Sila masukkan maklumat akaun anda</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf 
                    
                    <div class="input-group">
                        <label for="email">E-mel</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="contoh@ukm.edu.my" required value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Kata Laluan</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Masukkan kata laluan" required>
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('password.request') }}" class="forgot-password">Lupa Kata Laluan?</a>
                    </div>

                    <button type="submit" class="btn-primary">Log Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>