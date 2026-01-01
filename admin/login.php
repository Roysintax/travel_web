<?php
// =====================================================
// ADMIN LOGIN PAGE
// File: admin/login.php
// =====================================================

require_once 'includes/auth.php';

// Redirect jika sudah login
redirectIfLoggedIn();

$error = '';
$success = '';

// Cek flash message
$flash = getFlash();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    // Validasi
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // Cari admin
        $admin = getAdminByUsername($username);
        
        if (!$admin) {
            $error = 'Username tidak ditemukan!';
        } elseif (!$admin['is_active']) {
            $error = 'Akun Anda telah dinonaktifkan. Hubungi Super Admin.';
        } elseif (!verifyPassword($password, $admin['password'])) {
            $error = 'Password salah!';
        } else {
            // Login berhasil
            loginAdmin($admin);
            logAdminActivity('login', 'Login berhasil');
            
            // Set remember me jika dicentang
            if ($rememberMe) {
                $token = generateRememberToken();
                setRememberToken($admin['id'], $token);
            }
            
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Tanah Suci</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0F172A;
            --accent: #D4AF37;
            --accent-hover: #B5952F;
            --text-light: #F8FAFC;
            --text-dark: #334155;
            --bg-light: #F1F5F9;
            --error: #EF4444;
            --success: #22C55E;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, #1E293B 100%);
            padding: 1rem;
        }

        .login-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            background: var(--primary);
            padding: 2rem;
            text-align: center;
        }

        .login-header .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .login-header .logo span {
            color: var(--accent);
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .login-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #E2E8F0;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .form-check label {
            color: var(--text-dark);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: #DCFCE7;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748B;
            font-size: 0.9rem;
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #64748B;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: var(--accent);
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 1rem;
            }

            .login-header, .login-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">Tanah<span>Suci</span>.</div>
            <p>Admin Panel</p>
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Masukkan username" required autofocus
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Masukkan password" required>
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Ingat saya selama 30 hari</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Masuk
                </button>
            </form>
            
            <div class="footer-text">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
            </div>
            
            <a href="../index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Website
            </a>
        </div>
    </div>
</body>
</html>
