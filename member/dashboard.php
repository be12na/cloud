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

<div class="dash-wrapper">
    
    <?php if (hasFlash('success')): ?>
        <div style="max-width:960px;margin:0 auto 1.5rem;">
            <div class="alert-box alert-success">
                <span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span>
                <div><?php echo e(getFlash('success')); ?></div>
                <button class="alert-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-avatar">
            <?php echo e(strtoupper(mb_substr($user['name'], 0, 1))); ?>
        </div>
        <div>
            <h2>Selamat datang, <?php echo e($user['name']); ?>!</h2>
            <p>Dashboard Member Area &mdash; kelola akun dan akses fitur Anda.</p>
        </div>
    </div>

    <div class="dash-grid">
        <!-- Main Content -->
        <div>
            <div class="dash-card">
                <div class="dash-card-header">
                    <i class="bi bi-shield-check"></i> Status Akun
                </div>
                <div class="dash-card-body">
                    <p style="color:var(--gray-500);margin:0 0 1rem;font-size:.9rem;">
                        Akun Anda aktif dan berjalan normal. Hanya user dengan role 
                        <span class="badge-role"><i class="bi bi-star-fill"></i> member</span> 
                        yang dapat mengakses halaman ini.
                    </p>
                    <div style="background:var(--gray-50);border-radius:var(--radius-sm);padding:1rem;border:1px solid var(--gray-200);">
                        <div style="display:flex;align-items:center;gap:.5rem;color:var(--success);font-weight:600;font-size:.9rem;">
                            <i class="bi bi-check-circle-fill"></i> Akun terverifikasi &amp; aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Account Info -->
            <div class="dash-card" style="margin-bottom:1.5rem;">
                <div class="dash-card-header">
                    <i class="bi bi-person"></i> Informasi Akun
                </div>
                <div class="dash-card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-person"></i> Nama</span>
                        <span class="info-value"><?php echo e($user['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-shield"></i> Role</span>
                        <span class="badge-role"><i class="bi bi-star-fill"></i> <?php echo e($user['role']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-hash"></i> ID</span>
                        <span class="info-value">#<?php echo e((string) $user['id']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <i class="bi bi-lightning"></i> Aksi Cepat
                </div>
                <div class="dash-card-body">
                    <div class="action-list">
                        <a href="<?php echo e(SITE_BASE_URL ?: '/'); ?>" class="btn-outline-custom" style="width:100%;justify-content:center;">
                            <i class="bi bi-folder2-open"></i> Buka File Manager
                        </a>
                        <a href="<?php echo e(MEMBER_BASE_URL); ?>/logout.php" class="btn-outline-danger" style="width:100%;justify-content:center;">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
