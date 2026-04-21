<!DOCTYPE html>
    <html lang="en">
        <head> 
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{ asset('css/stylelogin.css') }}">
        </head>
        <body>
            <div class="container">
                
                <form action="{{ route('login') }}" method="POST">
                    @csrf 
                    <div class="logo-container">
                        <img src="{{ asset('pic/LogoRasmiUKM.png') }}" alt="logo UKM" class="logo">
                        <img src="{{ asset('pic/ftsm-black.png') }}" alt="logo FTSM" class="logo">
                        <img src="{{ asset('pic/logoIKOMblack-removebg-preview.png') }}" alt="logo I-KOM" class="logo">    
                    </div>

                    <h1>LOG MASUK</h1>

                    @if(session('success'))
                    <div id = "success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div id = "error">
                         {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="email">
                        <label for="email">E-mel:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="password-container">
                        <label for="password">Kata Laluan:</label>
                        <input type="password" id="password" name="password" required>
                        <span class="fa fa-fw fa-eye field-icon toggle-password"></span>
                    </div>

                    <div class="forgotpass">
                        <a href="{{ route('password.request') }}">Lupa Kata Laluan?</a>
                    </div>

                    <div class="submit-container">
                        <button type="submit" name="login_button" class="submitbutton">Log Masuk</button>
                    </div>

                </form>
            </div>
            <script>
                const togglePassword = document.querySelector('.toggle-password');
                const passwordField = document.querySelector('#password');

                togglePassword.addEventListener('click', function (e) {
                    // Toggle the type attribute
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Toggle the eye icon
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            </script>
        </body>
    </html>