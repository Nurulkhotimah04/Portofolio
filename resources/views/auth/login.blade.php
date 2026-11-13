<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SiPrioritas Pupuk</title>
    
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background:
                linear-gradient(
                    rgba(255,255,255,0.70),
                    rgba(255,255,255,0.70)
                ),
                url('{{ asset('img/bg-login.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-page {
            display: flex;
            min-height: 100vh;
        }

        .login-left {
            width: 55%;
            padding: 60px;
            color: #14532d;
            position: relative;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 70px;
        }

        .brand img {
            width: 80px;
        }

        .brand-title {
            font-size: 42px;
            font-weight: bold;
        }

        .brand-title span {
            color: #3aaa35;
        }

        .brand-subtitle {
            font-size: 18px;
            color: #555;
        }

        .hero-title {
            font-size: 34px;
            font-weight: bold;
            line-height: 1.3;
        }

        .hero-text {
            max-width: 520px;
            font-size: 16px;
            color: #333;
            line-height: 1.8;
        }

        .login-right {
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-right: 120px;
        }

        .login-card {
            width: 420px;
            background: rgba(255,255,255,0.96);
            padding: 45px;
            border-radius: 25px;
            backdrop-filter: blur(8px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.12);
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: #eef7ea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: #1c8c3a;
            font-size: 32px;
        }

        .login-title {
            text-align: center;
            color: #14532d;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .login-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            top: 16px;
            left: 18px;
            color: #777;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 48px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: #1f8f3a;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #166b2b;
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
        }

        .register-link a {
            color: #1f8f3a;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-page">
    <div class="login-left">
        <div class="brand">
            <div>
                <div class="brand-title">SiPrioritas <span>Pupuk</span></div>
                <div class="brand-subtitle">Sistem Prioritas Penerima Subsidi Pupuk Petani</div>
            </div>
        </div>

        <div class="hero-title">
            Tepat Sasaran,<br>
            Sejahterakan Petani
        </div>

        <p class="hero-text">
            SiPrioritas Pupuk membantu memastikan subsidi pupuk diberikan kepada petani
            yang berhak secara adil, transparan, dan akuntabel.
        </p>
    </div>

    <div class="login-right">
        <div class="login-card">
            <div class="login-icon">
                <i class="fas fa-lock"></i>
            </div>

            <div class="login-title">Selamat Datang</div>
            <div class="login-subtitle">Silakan masuk untuk melanjutkan</div>

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">Masuk</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '{{ $errors->first() }}',
            confirmButtonColor: '#1f8f3a',
            confirmButtonText: 'Coba Lagi'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
            confirmButtonColor: '#1f8f3a'
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#1f8f3a'
        });
    @endif
</script>

</body>
</html>