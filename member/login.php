<?php
/**
 * Login Member - /member/login.php
 * 
 * Form login member.
 * - CSRF protection
 * - Rate limiting (throttle 5 attempts per 5 menit per IP)
 * - Setelah berhasil: redirect ke dashboard atau intended URL
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

// Guard: hanya guest yang bisa akses halaman login
guardGuest();

$errors = [];
$pageTitle = 'Login Member';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verifikasi CSRF token
    if (!Csrf::verify()) {
        $errors['csrf'] = 'Sesi keamanan tidak valid. Silakan coba lagi.';
    } else {
        // 2. Rate limiting: max 5 login attempts per 5 menit per IP
        $rateLimiter = new RateLimit(5, 300);
        $clientIp = getClientIp();

        if (!$rateLimiter->isAllowed($clientIp, 'login')) {
            $retryAfter = $rateLimiter->getRetryAfter($clientIp, 'login');
            $errors['throttle'] = 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($retryAfter / 60) . ' menit.';
        } else {
            // Catat attempt
            $rateLimiter->record($clientIp, 'login');

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Simpan old input
            saveOldInput(['email' => $email]);

            if ($email === '' || $password === '') {
                $errors['general'] = 'Email dan password wajib diisi.';
            } else {
                // 3. Attempt login (password_verify + session regenerate)
                $user = Auth::attempt($email, $password);

                if ($user !== false) {
                    // Regenerate CSRF
                    Csrf::regenerate();
                    clearOldInput();

                    // Cek intended URL (jika user di-redirect dari halaman terproteksi)
                    $intended = $_SESSION['member_intended_url'] ?? null;
                    unset($_SESSION['member_intended_url']);

                    setFlash('success', 'Selamat datang kembali, ' . e($user['name']) . '!');

                    // Redirect ke intended URL atau dashboard
                    $redirectTo = $intended ?: MEMBER_BASE_URL . '/dashboard.php';
                    header('Location: ' . $redirectTo);
                    exit;

                } else {
                    // Pesan error generik (jangan beri tahu apakah email atau password yang salah)
                    $errors['general'] = 'Email atau password salah.';
                }
            }
        }
    }

    // Regenerate CSRF setelah gagal juga
    Csrf::regenerate();
}

// Render page
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header" style="background: linear-gradient(135deg, var(--dark), var(--dark-soft));">
            <div class="auth-icon"><i class="bi bi-box-arrow-in-right"></i></div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun member Anda</p>
        </div>
        <div class="auth-card-body">

            <?php if (hasFlash('error')): ?>
                <div class="alert-box alert-warning">
                    <span class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <div><?php echo e(getFlash('error')); ?></div>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (hasFlash('success')): ?>
                <div class="alert-box alert-success">
                    <span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span>
                    <div><?php echo e(getFlash('success')); ?></div>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-box alert-danger">
                    <span class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></span>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <?php echo Csrf::field(); ?>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-envelope form-input-icon"></i>
                        <input type="email" 
                               class="form-input" 
                               id="email" name="email" 
                               value="<?php echo old('email'); ?>" 
                               placeholder="nama@email.com"
                               required autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-lock form-input-icon"></i>
                        <input type="password" 
                               class="form-input" 
                               id="password" name="password" 
                               placeholder="Masukkan password"
                               required>
                        <button type="button" class="pw-toggle"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn-dark-custom">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>
        </div>
        <div class="auth-footer">
            Belum punya akun? <a href="<?php echo e(MEMBER_BASE_URL); ?>/register.php">Daftar sekarang</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
