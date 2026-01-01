<?php
// =====================================================
// ADMIN REGISTER PAGE
// File: admin/register.php
// =====================================================

require_once 'includes/auth.php';

// Redirect jika sudah login
redirectIfLoggedIn();

$errors = [];
$success = '';
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['username'] = trim($_POST['username'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validasi username
    if (empty($formData['username'])) {
        $errors['username'] = 'Username harus diisi!';
    } elseif (strlen($formData['username']) < 3) {
        $errors['username'] = 'Username minimal 3 karakter!';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $formData['username'])) {
        $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore!';
    } elseif (getAdminByUsername($formData['username'])) {
        $errors['username'] = 'Username sudah digunakan!';
    }
    
    // Validasi email
    if (empty($formData['email'])) {
        $errors['email'] = 'Email harus diisi!';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid!';
    } elseif (getAdminByEmail($formData['email'])) {
        $errors['email'] = 'Email sudah digunakan!';
    }
    
    // Validasi nama lengkap
    if (empty($formData['full_name'])) {
        $errors['full_name'] = 'Nama lengkap harus diisi!';
    }
    
    // Validasi password
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi!';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter!';
    }
    
    // Validasi konfirmasi password
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Konfirmasi password tidak cocok!';
    }
    
    // Jika tidak ada error, register admin
    if (empty($errors)) {
        $adminId = registerAdmin([
            'username' => $formData['username'],
            'email' => $formData['email'],
            'full_name' => $formData['full_name'],
            'password' => $password,
            'role' => 'editor' // Default role untuk keamanan
        ]);
        
        if ($adminId) {
            setFlash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
            header('Location: login.php');
            exit;
        } else {
            $errors['general'] = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - Tanah Suci</title>
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

        .register-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
        }

        .register-header {
            background: var(--primary);
            padding: 2rem;
            text-align: center;
        }

        .register-header .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .register-header .logo span {
            color: var(--accent);
        }

        .register-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .register-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
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

        .form-control.is-invalid {
            border-color: var(--error);
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #E2E8F0;
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }

        @media (max-width: 480px) {
            .register-container {
                border-radius: 1rem;
            }

            .register-header, .register-form {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">Tanah<span>Suci</span>.</div>
            <p>Daftar Akun Admin</p>
        </div>
        
        <div class="register-form">
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="full_name" name="full_name" 
                               class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                               placeholder="Masukkan nama lengkap" required
                               value="<?= htmlspecialchars($formData['full_name']) ?>">
                    </div>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               placeholder="Masukkan email" required
                               value="<?= htmlspecialchars($formData['email']) ?>">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-at"></i>
                        <input type="text" id="username" name="username" 
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               placeholder="Masukkan username" required
                               value="<?= htmlspecialchars($formData['username']) ?>">
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Min. 6 karakter" required>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Ulangi password" required>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="password-strength" id="passwordStrength" style="display: none;">
                    <span id="strengthText">Kekuatan password: </span>
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBar"></div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fa-solid fa-user-plus"></i>
                    Daftar Sekarang
                </button>
            </form>
            
            <div class="footer-text">
                Sudah punya akun? <a href="login.php">Login Sekarang</a>
            </div>
            
            <a href="../index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Website
            </a>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            const colors = ['#EF4444', '#F97316', '#EAB308', '#22C55E', '#16A34A'];
            const texts = ['Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];
            
            const index = Math.min(strength - 1, 4);
            strengthBar.style.width = widths[index] || '0%';
            strengthBar.style.background = colors[index] || '#E2E8F0';
            strengthText.textContent = 'Kekuatan password: ' + (texts[index] || 'Terlalu Pendek');
        });
    </script>
</body>
</html>
