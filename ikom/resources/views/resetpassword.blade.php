<!DOCTYPE html>
    <html lang="en">
        <head> 
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Kata Laluan</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{ asset('css/stylefogotresetpassword.css') }}">
        </head>
        <body>
            <div class="container">
                <form method="POST" action="{{ route('password.update') }}">
                    <div class="logo-container">
                        <img src="{{ asset('pic/LogoRasmiUKM.png') }}" alt="logo UKM" class="logo">
                        <img src="{{ asset('pic/ftsm-black.png') }}" alt="logo FTSM" class="logo">
                        <img src="{{ asset('pic/logoIKOMblack-removebg-preview.png') }}" alt="logo I-KOM" class="logo">    
                    </div>

                    <h1>Reset Kata Laluan</h1>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label for="email">E-mel:</label>
                    <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly>

                    <label for="password">Kata Laluan Baharu:</label>
                    <input type="password" name="password" required>

                    <label for="password_confirmation">Sahkan Kata Laluan Baharu:</label>
                    <input type="password" name="password_confirmation" required>

                    <div class="submit-container">
                        <button type="submit">Kemaskini Kata Laluan</button>
                    </div>

                    <div class="back-link">
                        <a href="{{ route('login') }}"><i class="fa fa-arrow-left"></i> Kembali ke Log Masuk</a>
                    </div>
                </form>
            </div>
        </body>
    </html>