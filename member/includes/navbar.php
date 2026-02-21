<?php
/**
 * Navbar - Menampilkan menu berdasarkan status login dan role.
 * 
 * - Guest: Register Member, Login
 * - Logged-in member: Dashboard, Logout
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo e(SITE_BASE_URL ?: '/'); ?>">
            <i class="bi bi-cloud-fill me-1"></i> Cloud
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#memberNavbar" 
                aria-controls="memberNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="memberNavbar">
            <ul class="navbar-nav ms-auto">
                <!-- Link ke File Manager -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(SITE_BASE_URL ?: '/'); ?>">
                        <i class="bi bi-folder2-open"></i> File Manager
                    </a>
                </li>

                <?php if (Auth::isGuest()): ?>
                    <!-- Guest Menu -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'register.php' ? 'active' : ''; ?>" 
                           href="<?php echo e(MEMBER_BASE_URL); ?>/register.php">
                            <i class="bi bi-person-plus"></i> Register Member
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'login.php' ? 'active' : ''; ?>"
                           href="<?php echo e(MEMBER_BASE_URL); ?>/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Logged-in Member Menu -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'dashboard.php' ? 'active' : ''; ?>"
                           href="<?php echo e(MEMBER_BASE_URL); ?>/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo e(Auth::user()['name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    <i class="bi bi-shield-check"></i> Role: <?php echo e(Auth::user()['role'] ?? ''); ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo e(MEMBER_BASE_URL); ?>/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
