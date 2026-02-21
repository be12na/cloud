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

<div class="container py-4">
    <div class="form-card">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Register Member</h4>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-triangle"></i> Error:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <?php echo Csrf::field(); ?>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-person"></i> Nama <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" 
                               value="<?php echo old('name'); ?>" 
                               placeholder="Nama lengkap (min. 2 karakter)"
                               required minlength="2" maxlength="100" autofocus>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" 
                               value="<?php echo old('email'); ?>" 
                               placeholder="contoh@email.com"
                               required maxlength="255">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo e($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   id="password" name="password" 
                                   placeholder="Minimal 8 karakter"
                                   required minlength="8" maxlength="255">
                            <button class="btn btn-outline-secondary password-toggle" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['password']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            <i class="bi bi-lock-fill"></i> Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control <?php echo isset($errors['password_confirmation']) ? 'is-invalid' : ''; ?>" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Ulangi password"
                                   required minlength="8" maxlength="255">
                            <button class="btn btn-outline-secondary password-toggle" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (isset($errors['password_confirmation'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['password_confirmation']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Register
                        </button>
                    </div>
                </form>

                <hr>
                <p class="text-center text-muted mb-0">
                    Sudah punya akun? 
                    <a href="<?php echo e(MEMBER_BASE_URL); ?>/login.php">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
