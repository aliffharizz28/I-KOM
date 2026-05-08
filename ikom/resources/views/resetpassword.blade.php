<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Laluan | I-KOM</title>
    <link rel="icon" type="image/png" href="{{ asset('pic/logoikomputih.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stylefogotresetpassword.css') }}?v={{ time() }}">
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('password.update') }}">
            <h1>Reset Kata Laluan</h1>
            <p class="subtitle">Sila cipta kata laluan baharu anda</p>

            @if (session('error'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group">
                <label for="email">E-mel</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required readonly>
                </div>
            </div>

            <div class="input-group">
                <label for="password">Kata Laluan Baharu</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" required placeholder="Masukkan kata laluan baharu">
                </div>
            </div>

            <div class="input-group">
                <label for="password_confirmation">Sahkan Kata Laluan Baharu</label>
                <div class="input-wrapper">
                    <i class="fas fa-check-circle input-icon"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Sahkan kata laluan">
                </div>
            </div>

            <button type="submit" class="btn-primary">Kemaskini Kata Laluan</button>

            <div class="back-link">
                <a href="{{ route('login') }}"><i class="fa fa-arrow-left"></i> Kembali ke Log Masuk</a>
            </div>
        </form>
    </div>
</body>
</html>