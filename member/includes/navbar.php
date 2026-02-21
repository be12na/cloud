<?php
/**
 * Navbar - Modern responsive navbar
 * Menu dinamis berdasarkan status login dan role.
 */
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="m-navbar">
    <div class="container">
        <a class="m-navbar-brand" href="<?php echo e(SITE_BASE_URL ?: '/'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>
            </svg>
            Cloud
        </a>
        <button class="m-navbar-toggle" onclick="this.nextElementSibling.classList.toggle('open')" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <ul class="m-nav">
            <li><a href="<?php echo e(SITE_BASE_URL ?: '/'); ?>"><i class="bi bi-folder2-open"></i> File Manager</a></li>
            <li><span class="nav-divider"></span></li>

            <?php if (Auth::isGuest()): ?>
                <li>
                    <a href="<?php echo e(MEMBER_BASE_URL); ?>/register.php" <?php echo $currentPage === 'register.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                </li>
                <li>
                    <a href="<?php echo e(MEMBER_BASE_URL); ?>/login.php" <?php echo $currentPage === 'login.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?php echo e(MEMBER_BASE_URL); ?>/dashboard.php" <?php echo $currentPage === 'dashboard.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <div class="nav-user" onclick="document.getElementById('navDropdown').classList.toggle('show')">
                        <div class="nav-avatar"><?php echo e(strtoupper(mb_substr(Auth::user()['name'] ?? '?', 0, 1))); ?></div>
                        <?php echo e(Auth::user()['name'] ?? 'User'); ?>
                        <i class="bi bi-chevron-down" style="font-size:.7rem;opacity:.6"></i>
                        <div class="nav-dropdown" id="navDropdown">
                            <div class="dd-label">Akun</div>
                            <a href="<?php echo e(MEMBER_BASE_URL); ?>/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            <div class="dd-divider"></div>
                            <a href="<?php echo e(MEMBER_BASE_URL); ?>/logout.php" class="dd-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
