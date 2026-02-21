<?php
/**
 * Dashboard Member - /member/dashboard.php
 * 
 * Halaman ini HANYA bisa diakses oleh user yang:
 * 1. Sudah login
 * 2. Memiliki role = "member"
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

// Guard: Hanya user login dengan role "member"
guardRole('member');

$user = Auth::user();
$pageTitle = 'Dashboard Member';

// Render page
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    
    <?php if (hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(getFlash('success')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Welcome Card -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <?php echo e(strtoupper(mb_substr($user['name'], 0, 1))); ?>
                        </div>
                        <div>
                            <h3 class="mb-0">Selamat datang, <?php echo e($user['name']); ?>!</h3>
                            <p class="text-muted mb-0">Dashboard Member Area</p>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted">
                        Ini adalah halaman dashboard member Anda. Hanya user dengan role 
                        <span class="badge bg-primary">member</span> yang dapat mengakses halaman ini.
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-info-circle"></i> Informasi Akun
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted"><i class="bi bi-person"></i> Nama</td>
                            <td><strong><?php echo e($user['name']); ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-shield"></i> Role</td>
                            <td><span class="badge bg-primary"><?php echo e($user['role']); ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="bi bi-hash"></i> ID</td>
                            <td><?php echo e((string) $user['id']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-lightning"></i> Aksi Cepat
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="<?php echo e(SITE_BASE_URL ?: '/'); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-folder2-open"></i> Buka File Manager
                    </a>
                    <a href="<?php echo e(MEMBER_BASE_URL); ?>/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
