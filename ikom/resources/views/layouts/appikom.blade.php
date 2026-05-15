<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-KOM @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('pic/logoikomputih.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/stylesidebar.css') }}?v=2.0">
    <style>
        /* CSS Tambahan untuk mengawal layout besar */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Elakkan scroll horizontal */
            font-family: 'Inter', sans-serif; /* Set default as Inter */
            color: #333;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif; /* Set headers as Montserrat */
        }
        .main-layout {
            display: flex; /* Memastikan sidebar dan content sebelah-menyebelah */
            min-height: 100vh;
            width: 100%;
        }
        .content-area {
            flex: 1;
            margin-left: 280px; /* Match sidebar width */
            background-color: #f8f9fa;
            padding: 40px;
            box-sizing: border-box;
            transition: margin-left 0.3s ease-in-out;
        }
        /* When sidebar is collapsed, content fills the full width */
        .content-area.sidebar-hidden {
            margin-left: 0;
        }
    </style>
</head>
<body>
    <div class="main-layout">
        
        @include('sidebar')

        <div class="content-area">
            @include('topbar')
            @yield('content')
            @include('bottombar')
        </div>

    </div>
</body>
</html>