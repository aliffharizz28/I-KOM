<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Laluan? | I-KOM</title>
    <link rel="icon" type="image/png" href="{{ asset('pic/logoikomputih.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stylefogotresetpassword.css') }}?v={{ time() }}">
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('password.email') }}">
            <h1>Lupa Kata Laluan?</h1>
            <p class="subtitle">Sila masukkan e-mel anda untuk pautan penetapan semula</p>

            @if (session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            @csrf
            
            <div class="input-group">
                <label for="email">E-mel</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="contoh@ukm.edu.my">
                </div>
            </div>

            <button type="submit" class="btn-primary">Hantar Pautan</button>

            <div class="back-link">
                <a href="{{ route('login') }}"><i class="fa fa-arrow-left"></i> Kembali ke Log Masuk</a>
            </div>
        </form>
    </div>
</body>
</html>