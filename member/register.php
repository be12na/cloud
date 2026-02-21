<?php
/**
 * Register Member - /member/register.php
 * 
 * Form registrasi member baru.
 * - Validasi: name min 2 char, email unique & valid, password min 8 char + confirmed
 * - CSRF protection
 * - Rate limiting (throttle 5 attempts per 5 menit per IP)
 * - Setelah berhasil: auto-login dan redirect ke dashboard
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

// Guard: hanya guest yang bisa akses halaman register
guardGuest();

$errors = [];
$pageTitle = 'Register Member';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verifikasi CSRF token
    if (!Csrf::verify()) {
        $errors['csrf'] = 'Sesi keamanan tidak valid. Silakan coba lagi.';
    } else {
        // 2. Rate limiting: max 5 register attempts per 5 menit per IP
        $rateLimiter = new RateLimit(5, 300);
        $clientIp = getClientIp();

        if (!$rateLimiter->isAllowed($clientIp, 'register')) {
            $retryAfter = $rateLimiter->getRetryAfter($clientIp, 'register');
            $errors['throttle'] = 'Terlalu banyak percobaan registrasi. Coba lagi dalam ' . ceil($retryAfter / 60) . ' menit.';
        } else {
            // Catat attempt
            $rateLimiter->record($clientIp, 'register');

            // 3. Sanitize & Validate input
            $input = [
                'name'                  => trim($_POST['name'] ?? ''),
                'email'                 => trim($_POST['email'] ?? ''),
                'password'              => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? '',
            ];

            // Simpan old input (tanpa password)
            saveOldInput($input);

            $validator = new Validator($input);
            $validator->validateName('name')
                      ->validateEmail('email')
                      ->validatePassword('password', 'password_confirmation');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            } else {
                // 4. Register user (password_hash + auto-login)
                try {
                    Auth::register($input['name'], $input['email'], $input['password'], 'member');

                    // Regenerate CSRF token setelah register
                    Csrf::regenerate();
                    clearOldInput();

                    setFlash('success', 'Registrasi berhasil! Selamat datang, ' . e($input['name']) . '.');

                    // Redirect ke dashboard
                    header('Location: ' . MEMBER_BASE_URL . '/dashboard.php');
                    exit;

                } catch (PDOException $e) {
                    // Duplicate email (race condition)
                    if ($e->getCode() == 23000) {
                        $errors['email'] = 'Email sudah terdaftar.';
                    } else {
                        error_log('Register error: ' . $e->getMessage());
                        $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
                    }
                } catch (RuntimeException $e) {
                    error_log('Register error: ' . $e->getMessage());
                    $errors['general'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
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
        <div class="auth-card-header">
            <div class="auth-icon"><i class="bi bi-person-plus-fill"></i></div>
            <h2>Buat Akun Baru</h2>
            <p>Daftar untuk mendapatkan akses member</p>
        </div>
        <div class="auth-card-body">

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

                <!-- Name -->
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-person form-input-icon"></i>
                        <input type="text" 
                               class="form-input <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" 
                               value="<?php echo old('name'); ?>" 
                               placeholder="Masukkan nama lengkap"
                               required minlength="2" maxlength="100" autofocus>
                    </div>
                    <?php if (isset($errors['name'])): ?>
                        <div class="input-feedback"><i class="bi bi-exclamation-circle"></i> <?php echo e($errors['name']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-envelope form-input-icon"></i>
                        <input type="email" 
                               class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" 
                               value="<?php echo old('email'); ?>" 
                               placeholder="nama@email.com"
                               required maxlength="255">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="input-feedback"><i class="bi bi-exclamation-circle"></i> <?php echo e($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-lock form-input-icon"></i>
                        <input type="password" 
                               class="form-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" 
                               placeholder="Minimal 8 karakter"
                               required minlength="8" maxlength="255">
                        <button type="button" class="pw-toggle"><i class="bi bi-eye"></i></button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="input-feedback"><i class="bi bi-exclamation-circle"></i> <?php echo e($errors['password']); ?></div>
                    <?php endif; ?>
                    <div class="pw-strength">
                        <div class="pw-bar"><div class="pw-bar-fill"></div></div>
                        <div class="pw-text"></div>
                    </div>
                </div>

                <!-- Password Confirmation -->
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="form-input-wrap">
                        <i class="bi bi-lock-fill form-input-icon"></i>
                        <input type="password" 
                               class="form-input <?php echo isset($errors['password_confirmation']) ? 'is-invalid' : ''; ?>" 
                               id="password_confirmation" name="password_confirmation" 
                               placeholder="Ulangi password"
                               required minlength="8" maxlength="255">
                        <button type="button" class="pw-toggle"><i class="bi bi-eye"></i></button>
                    </div>
                    <?php if (isset($errors['password_confirmation'])): ?>
                        <div class="input-feedback"><i class="bi bi-exclamation-circle"></i> <?php echo e($errors['password_confirmation']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i> Buat Akun
                </button>
            </form>
        </div>
        <div class="auth-footer">
            Sudah punya akun? <a href="<?php echo e(MEMBER_BASE_URL); ?>/login.php">Login di sini</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
