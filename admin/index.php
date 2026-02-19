<?php
/**
 * VFM Admin Panel - Optimized Version
 * PHP version >= 5.4
 */

define('VFM_APP', true);
require_once 'admin-panel/view/admin-head.php';

// Definisikan path dasar untuk view admin
 $admin_view_path = __DIR__ . '/admin-panel/view';

// Update usernames prior to v 2.6.3 to unsensitive
if (file_exists('_unsensitive-users.php')) {
    if (count($_USERS) > 1 && version_compare(VFM_VERSION, '2.6.3', '>')) {
        include '_unsensitive-users.php';
    } else {
        unlink('_unsensitive-users.php');
    }
}

// User available quota (in MB)
 $_QUOTA = [
    "10", "20", "50", "100", "200", "500",
    "1024", // 1GB
    "2048", // 2GB
    "5120", // 5GB
];

// Expiration for downloadable links
 $share_lifetime = [
    "1" => "24 h",
    "2" => "48 h",
    "3" => "72 h",
    "5" => "5 days",
    "7" => "7 days",
    "10" => "10 days",
    "30" => "30 days",
    "365" => "1 year",
    "36500" => "Unlimited",
];

// Expiration for registration links
 $registration_lifetime = [
    "-1 hour" => "1 h",
    "-3 hours" => "3 h",
    "-6 hours" => "6 h",
    "-12 hours" => "12 h",
    "-1 day" => "1 day",
    "-2 days" => "2 days",
    "-7 days" => "7 days",
    "-1 month" => "30 days",
];

 $allroles = [];
require $admin_view_path . '/users/roles.php';

if (is_array($getroles)) {
    foreach ($getroles as $role) {
        $allroles[$role] = $setUp->getString("role_".$role);
    }
}

 $allroles_nosuperadmin = $allroles;
unset($allroles_nosuperadmin['superadmin']);

 $rtl_ext = '';
 $rtl_att = '';
 $rtl_class = '';
if ($setUp->getConfig("txt_direction") == "RTL") {
    $rtl_att = ' dir="rtl"';
    $rtl_ext = '.rtl';
    $rtl_class = ' rtl';
}
?>
<!doctype html>
<html lang="<?php echo $setUp->lang; ?>"<?php echo $rtl_att; ?>>
<head>
    <title><?php print $setUp->getString('admin')." | ".$setUp->getConfig('appname'); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php echo $setUp->printIcon("_content/uploads/"); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap<?php echo $rtl_ext; ?>.min.css">
    <link rel="stylesheet" href="icons/bootstrap-icons.min.css">

    <?php
    // Optimasi Switch Case CSS
    switch ($get_section) {
        case 'appearance':
            echo '<link rel="stylesheet" href="admin-panel/plugins/spectrum/spectrum.min.css">';
            echo '<link rel="stylesheet" href="admin-panel/plugins/easyeditor/easyeditor.css">';
            break;
        case 'users':
        case 'logs':
            echo '<link rel="stylesheet" href="assets/datatables/datatables.min.css">';
            if ($get_section === 'users') {
                echo '<link rel="stylesheet" href="admin-panel/plugins/bootstrap-select/css/bootstrap-select.min.css">';
            }
            break;
        default:
            echo '<link rel="stylesheet" href="admin-panel/plugins/tagin/tagin.min.css">';
            echo '<link rel="stylesheet" href="admin-panel/plugins/bootstrap-select/css/bootstrap-select.min.css">';
            break;
    }
    ?>
   <link rel="stylesheet" href="admin-panel/css/admin.css?v=<?php echo VFM_VERSION; ?>">
   <?php
    // Optimasi Cache CSS: Gunakan filemtime agar browser cache file lama,
    // tapi update otomatis jika file berubah.
    $colors_css_path = file_exists('_content/template/colors.css') ? '_content/template/colors.css' : 'css/colors.css';
    if (file_exists($colors_css_path)) {
        $css_ver = filemtime($colors_css_path);
        ?>
        <link rel="stylesheet" href="<?php echo $colors_css_path; ?>?v=<?php echo $css_ver; ?>">
        <?php
    }
    ?>
    <script type="text/javascript" src="assets/jquery/jquery-3.6.1.min.js"></script>
</head>

<?php
 $skin = $setUp->getConfig('admin_color_scheme') ?: 'blue';
 $scrollspy_data = $activesec == "home" ? ' data-bs-spy="scroll" data-bs-target="#sidebar-nav" data-bs-offset="0" tabindex="0"' : '';
?>
<body class="fixed sidebar-mini admin-body<?php echo $rtl_class; ?>"<?php echo $scrollspy_data; ?>>

<header class="navbar fixed-top bg-dark flex-md-nowrap shadow navbar-expand">
    <div class="container-fluid">
        <a class="navbar-brand me-0 px-3 flex-grow-1" href="<?php echo $setUp->getConfig('script_url'); ?>admin/"><?php print $setUp->getConfig('appname'); ?></a>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav ms-auto">
                <li class="nav-item d-inline-block d-md-none">
                    <button class="toggle-sidebar btn btn-link ms-auto" type="button" data-bs-target=".supercontainer">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $setUp->getConfig('script_url'); ?>"><i class="bi bi-house-door"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cloudSettingsModal" title="Cloud Settings"><i class="bi bi-cloud-fill"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $setUp->getConfig('script_url'); ?>?logout" title="<?php echo $setUp->getString("log_out"); ?>"><i class="bi bi-box-arrow-right"></i> </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-flag"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark lang-menu">
                        <?php print ($setUp->printLangMenu()); ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="supercontainer row g-0">

    <?php require $admin_view_path . '/sidebar.php'; ?>
    
    <main class="main bg-light d-flex flex-column justify-content-between min-vh-100">
        <div id="view-preferences"></div>
        <div class="content-wrapper px-3 px-md-4 pt-5 mb-auto">
            <?php
            switch ($get_section) {

            case 'updates':
                if ($gateKeeper->canSuperAdmin('superadmin_can_updates')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-arrow-repeat"></i> <?php print $setUp->getString("updates"); ?></h2>
                </div>
                    <?php echo $admin->printAlert(); ?>
                <div class="content">
                    <?php include $admin_view_path . '/updater/index.php'; ?>
                </div>
                <?php }
                break;

            case 'appearance':
                if ($gateKeeper->canSuperAdmin('superadmin_can_appearance')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-brush"></i> <?php print $setUp->getString("appearance"); ?></h2>
                </div>
                    <?php echo $admin->printAlert(); ?>
                <div class="content">
                    <form role="form" method="post" id="settings-form" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>?section=appearance" enctype="multipart/form-data">
                    <?php
                    include $admin_view_path . '/appearance/appearance.php';
                    include $admin_view_path . '/save-settings.php'; ?>
                    </form>
                </div>
                <?php }
                break;

            case 'users':
                if ($gateKeeper->canSuperAdmin('superadmin_can_users')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-people"></i> <?php print $setUp->getString("users"); ?></h2>
                </div>
                    <?php echo $admin->printAlert(); ?>
                <div class="content body">
                    <div class="row">
                        <?php
                        include $admin_view_path . '/users/new-user.php';
                        if ($gateKeeper->isMasterAdmin()) {
                            include $admin_view_path . '/users/master-admin.php';
                        }
                        ?>
                    </div>
                    <?php
                    include $admin_view_path . '/users/list-users.php';
                    include $admin_view_path . '/users/modal-user.php';
                    ?>
                </div>
                <?php }
                break;

            case 'translations':
                if ($gateKeeper->canSuperAdmin('superadmin_can_translations')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-translate"></i> <?php print $setUp->getString("language_manager"); ?></h2>
                </div>
                    <?php echo $admin->printAlert(); ?>
                <div class="content">
                    <?php
                    if ($get_action == 'edit') {
                        if ($editlang || ($postnewlang && strlen($postnewlang) == 2 && !array_key_exists($postnewlang, $translations))) {
                            include $admin_view_path . '/language/edit.php';
                        }
                    } else {
                        include $admin_view_path . '/language/panel.php';
                    }
                    ?>
                </div>
                <?php }
                break;

            case 'logs':
                if ($gateKeeper->canSuperAdmin('superadmin_can_statistics')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-graph-up-arrow"></i> <?php print $setUp->getString("statistics"); ?></h2>
                </div>
                    <?php echo $admin->printAlert(); ?>
                <div class="content">
                    <?php
                    include $admin_view_path . '/analytics/selector.php';
                    include $admin_view_path . '/analytics/charts.php';
                    include $admin_view_path . '/analytics/table.php';
                    include $admin_view_path . '/analytics/range.php';
                    ?>
                </div>
                <?php }
                break;

            default:
                if ($gateKeeper->canSuperAdmin('superadmin_can_preferences')) { ?>
                <div class="content-header pt-5">
                    <h2 class="mb-4"><i class="bi bi-sliders"></i> <?php print $setUp->getString("preferences"); ?></h2>
                </div>
                <?php echo $admin->printAlert(); ?>
                <div class="content">
                    <form class="position-relative" role="form" method="post" id="settings-form" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" enctype="multipart/form-data">
                        <?php
                        include $admin_view_path . '/dashboard/general.php';
                        include $admin_view_path . '/dashboard/uploads.php';
                        include $admin_view_path . '/dashboard/lists.php';
                        include $admin_view_path . '/dashboard/permissions.php';
                        include $admin_view_path . '/dashboard/registration.php';
                        include $admin_view_path . '/dashboard/share.php';
                        include $admin_view_path . '/dashboard/email.php';
                        include $admin_view_path . '/dashboard/security.php';
                        include $admin_view_path . '/dashboard/activities.php';
                        include $admin_view_path . '/save-settings.php';
                        ?>
                        <div class="form-group">       
                            <?php $debugchecked = $setUp->getConfig('debug_mode') ? ' checked' : ''; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="debug_mode" value="1" id="check-debug"<?php echo $debugchecked; ?>>
                                <label class="form-check-label" for="check-debug">
                                    <i class="bi bi-wrench-adjustable"></i> DEBUG MODE <a title="Display all PHP notices" class="tooltipper" data-bs-placement="right" data-bs-toggle="tooltip" href="javascript:void(0)"><i class="bi bi-question-circle"></i></a>
                                </label>
                            </div>
                        </div>
                    </form>
                </div> <!-- content -->
                <?php
                } else {
                        $username = $gateKeeper->getUserInfo('name');
                    ?>
                <div class="content">
                    <h2 class="mb-4"><?php echo $gateKeeper->getAvatar($username, '').' <a href="'.$setUp->getConfig('script_url').'">'.$username.'</a>'; ?></h2>
                </div>
                <?php }
                break;
            } ?>
            <br>
            <br>
            <br>
        </div> <!-- content-wrapper -->
        <?php
        require $admin_view_path . '/footer.php';
        if ($get_section == 'logs') {
            include $admin_view_path . '/analytics/loader.php';
        }
        ?>
    </main>
</div> <!-- supercontainer -->

<!-- Cloud Settings Modal -->
<div class="modal fade" id="cloudSettingsModal" tabindex="-1" aria-labelledby="cloudSettingsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cloudSettingsLabel"><i class="bi bi-cloud-fill me-2"></i>Cloud Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="cloudSettingsAlert" class="d-none"></div>
        <form id="cloudSettingsForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo Utils::generateCsrfToken(); ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold" for="cs_appname">
                    <i class="bi bi-tag me-1"></i>Nama Cloud
                </label>
                <input type="text" class="form-control" id="cs_appname" name="appname" 
                       value="<?php echo htmlspecialchars($setUp->getConfig('appname')); ?>" 
                       placeholder="Cloud" required>
                <div class="form-text">Nama yang ditampilkan di header, title browser, dan footer.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="cs_script_url">
                    <i class="bi bi-link-45deg me-1"></i>Link Domain
                </label>
                <input type="url" class="form-control" id="cs_script_url" name="script_url" 
                       value="<?php echo htmlspecialchars($setUp->getConfig('script_url')); ?>" 
                       placeholder="https://cloud.example.com/" required>
                <div class="form-text">URL lengkap domain termasuk <code>https://</code> dan akhiri dengan <code>/</code></div>
            </div>
            <div class="bg-light rounded p-3 mb-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Perubahan domain akan mempengaruhi semua link di aplikasi termasuk logout, share link, dan redirect.
                </small>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveCloudSettings">
            <i class="bi bi-check-circle me-1"></i>Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnSave = document.getElementById('btnSaveCloudSettings');
    var form = document.getElementById('cloudSettingsForm');
    var alertBox = document.getElementById('cloudSettingsAlert');

    if (btnSave) {
        btnSave.addEventListener('click', function() {
            var appname = document.getElementById('cs_appname').value.trim();
            var scriptUrl = document.getElementById('cs_script_url').value.trim();

            if (appname.length < 1) {
                showAlert('danger', 'Nama Cloud wajib diisi.');
                return;
            }
            if (scriptUrl.length < 5) {
                showAlert('danger', 'Link Domain wajib diisi.');
                return;
            }

            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

            var formData = new FormData(form);

            fetch('ajax/save-cloud-settings.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    showAlert('success', '<i class="bi bi-check-circle me-1"></i>' + data.message);
                    document.querySelector('.navbar-brand').textContent = data.appname;
                    setTimeout(function() {
                        window.location.href = data.script_url + 'admin/';
                    }, 1500);
                } else {
                    showAlert('danger', '<i class="bi bi-exclamation-triangle me-1"></i>' + data.message);
                }
            })
            .catch(function(err) {
                showAlert('danger', '<i class="bi bi-exclamation-triangle me-1"></i>Gagal menyimpan: ' + err.message);
            })
            .finally(function() {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="bi bi-check-circle me-1"></i>Simpan';
            });
        });
    }

    function showAlert(type, msg) {
        alertBox.className = 'alert alert-' + type + ' mb-3';
        alertBox.innerHTML = msg;
    }
});
</script>

</body>
</html>