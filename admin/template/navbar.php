<?php
/**
 * user menu, user panel and language selector
 *
 * PHP version >= 5.3
 *
 *
 */
if (!defined('VFM_APP')) {
    return;
}

$stepback = 'admin/';
$navbarclass = $setUp->getConfig("header_position") == 'above' ? '' : ' fixed-top'
?>
<nav class="navbar bg-dark navbar-expand-lg shadow<?php echo $navbarclass; ?>">
    <div class="container">
            <?php // Brand button
            if (!$setUp->getConfig('hide_logo', false)) {
                ?>
            <a class="navbar-brand" href="<?php echo $setUp->getConfig("script_url"); ?>">
                <?php
                if ($setUp->getConfig('navbar_logo')) { ?>
                    <img src="<?php echo $stepback.'_content/uploads/'.$setUp->getConfig('navbar_logo'); ?>">
                    <?php
                } else {
                    echo $setUp->getConfig("appname");
                } ?>
            </a>
                <?php
            } ?>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-vfm-menu" aria-controls="collapse-vfm-menu" aria-expanded="false" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>
        <div class="collapse navbar-collapse" id="collapse-vfm-menu">
            <ul class="navbar-nav ms-auto">
<?php
// User menu.
if ($gateKeeper->isUserLoggedIn()) {
    $username = $gateKeeper->getUserInfo('name');
    $avaimg = $gateKeeper->getAvatar($username, $stepback);

    if ($setUp->getConfig("show_usermenu") == true) { ?>
    <li class="nav-item">
        <a class="nav-link edituser" href="#" data-bs-toggle="modal" data-bs-target="#userpanel">
            <?php echo $avaimg; ?> 
            <span class="d-inline-block">
                <?php echo $username; ?>
            </span>
        </a>
    </li>
        <?php
    }
    if ($gateKeeper->isSuperAdmin()) { ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $setUp->getConfig("script_url"); ?>admin/">
            <i class="bi bi-sliders"></i> <?php echo $setUp->getString("admin"); ?>
        </a>
    </li>
        <?php
    } ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $setUp->getConfig("script_url").$location->makeLink(true, null, ""); ?>">
            <i class="bi bi-box-arrow-right"></i> <?php echo $setUp->getString("log_out"); ?>
        </a>
    </li>
    <?php
} else { // end logged user
    ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo $setUp->getConfig("script_url"); ?>">
            <i class="bi bi-box-arrow-right"></i> <?php echo $setUp->getString("log_in"); ?>
        </a>
    </li>
    <?php
}

// Global search
if ($setUp->getConfig('global_search') && $gateKeeper->isAccessAllowed() && $gateKeeper->isAllowed('viewdirs_enable')) { ?>
    <li class="nav-item">
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#global-search">
            <i class="bi bi-search"></i> 
                <?php echo $setUp->getString("search"); ?>
        </a>
    </li>
    <?php
}

// Language selector
if ($setUp->getConfig('show_langmenu')) { ?>
    <li class="dropdown nav-item">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
            <i class="bi bi-flag"></i>
            <?php
            if ($setUp->getConfig('show_langname')) {
                echo $setUp->getString("LANGUAGE_NAME");
            } else {
                echo $setUp->getString("language");
            } ?> 
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end lang-menu">
            <?php echo $setUp->printLangMenu($stepback); ?>
        </ul>
    </li>
    <?php
} ?>
            </ul>
        </div>
    </div>
</nav>

<?php
/**
 * Global search
 */
if ($setUp->getConfig('global_search') && $gateKeeper->isAccessAllowed() && $gateKeeper->isAllowed('viewdirs_enable')) { ?>
        <div class="modal fade" id="global-search" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-zoom-in"></i> <?php echo $setUp->getString("global_search"); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="search-form" class="disabled">
                            <div class="input-group input-group-lg mb-2">
                                <input class="form-control" id="s-input" type="text" name="s" placeholder="<?php echo $setUp->getString("search"); ?>...">
                                <button class="btn btn-primary submit-search disabled" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                            <div class="modal_response">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php
} ?>

<?php
/**
 * User Panel
 */
if ($gateKeeper->isUserLoggedIn() && $setUp->getConfig("show_usermenu") == true) {
    /**
     * Get additional custom fields
     */
    $customfields = false;
    if (file_exists('admin/_content/users/customfields.php')) {
        include 'admin/_content/users/customfields.php';
    } ?>

    <div class="modal userpanel fade" id="userpanel" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <ul class="nav nav-pills nav-fill mb-3" role="tablist">

            <?php
            // additional custom fields.
            $profileTabActive = ' active';
            $profilePanelActive = ' show active';
            if (is_array($customfields) && !empty($customfields)) {
                $profileTabActive = '';
                $profilePanelActive = '';
                ?>
              <li role="presentation" class="nav-item">
                <button class="nav-link active" data-bs-target="#customfields" aria-controls="home" role="tab" data-bs-toggle="pill">
                    <i class="bi bi-card-list"></i> <?php echo $setUp->getString("informations"); ?>
                </button>
              </li>
                <?php
            } ?>
              <li role="presentation" class="nav-item">
                <button class="nav-link<?php echo $profileTabActive; ?>" data-bs-target="#upprof" aria-controls="home" role="tab" data-bs-toggle="pill">
                    <i class="bi bi-pencil-square"></i> 
                    <?php echo $setUp->getString("update_profile"); ?>
                </button>
              </li>
              <li role="presentation" class="nav-item">
                <button class="nav-link" data-bs-target="#upava" aria-controls="home" role="tab" data-bs-toggle="pill">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo $setUp->getString("avatar"); ?>
                </button>
              </li>
              <?php if ($gateKeeper->isSuperAdmin()) { ?>
              <li role="presentation" class="nav-item">
                <button class="nav-link" data-bs-target="#cloudsettings" aria-controls="cloudsettings" role="tab" data-bs-toggle="pill">
                    <i class="bi bi-cloud-fill"></i> Cloud Settings
                </button>
              </li>
              <?php } ?>
            </ul>

            <form role="form" method="post" id="usrForm" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);?>">
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane fade text-center" id="upava" role="tabpanel">
                <div class="avatar-response"></div>
                <div class="avatar-panel">
                    <div class="updated"></div>
                    <img class="avadefault rounded-circle" data-name="<?php echo $gateKeeper->getUserInfo('name'); ?>" />
                    <div class="cropit-preview"></div>
                    <i class="bi bi-x-lg text-muted remove-avatar"></i>
                    <input type="range" class="cropit-image-zoom-input slider" />
                    <input type="file" id="uppavatar" class="cropit-image-input">
                </div>

                <!-- And clicking on this button will open up select file dialog -->
                <div class="select-image-btn uppa btn btn-primary">
                    <?php echo $setUp->getString("upload"); ?> <i class="bi bi-upload"></i>
                </div>
                <div class="export btn btn-primary d-none">
                    <?php echo $setUp->getString("update"); ?> <i class="bi bi-check-circle"></i>
                </div> 
                <input type="hidden" class="image-name" value="<?php echo md5($gateKeeper->getUserInfo('name')); ?>">
              </div> <!-- tabpanel -->

              <div role="tabpanel" class="tab-pane fade<?php echo $profilePanelActive; ?>" id="upprof" role="tabpanel">
                  <div class="form-group mb-3">
                    <label class="form-label" for="user_new_name">
                        <?php echo $setUp->getString("username"); ?>
                    </label>
                    <input name="user_old_name" type="hidden" readonly class="form-control" value="<?php echo $gateKeeper->getUserInfo('name'); ?>">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input name="user_new_name" type="text" class="form-control" value="<?php echo $gateKeeper->getUserInfo('name'); ?>">
                    </div>
                    <label class="form-label" for="user_new_email">
                        <?php echo $setUp->getString("email"); ?>
                    </label>
                    <input name="user_old_email" type="hidden" readonly class="form-control" value="<?php echo $gateKeeper->getUserInfo('email'); ?>">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input name="user_new_email" type="text" class="form-control" value="<?php echo $gateKeeper->getUserInfo('email'); ?>">
                    </div>
                    <label class="form-label" for="user_new_pass">
                        <?php echo $setUp->getString("new_password"); ?>
                    </label>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input name="user_new_pass" id="newp" type="password" class="form-control">
                    </div>
                    <label class="form-label" for="user_new_pass_confirm">
                        <?php echo $setUp->getString("new_password")." (".$setUp->getString("confirm").")"; ?>
                    </label>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input name="user_new_pass_confirm" id="checknewp" type="password" class="form-control">
                    </div>
                </div>
              </div> <!-- tabpanel -->
    <?php
    /**
     * Set additional custom fields
     */
    if (is_array($customfields) && !empty($customfields)) {
        $customlist = htmlspecialchars(json_encode($customfields)); ?>
              <div role="tabpanel" class="tab-pane fade show active" id="customfields">
                <input type="hidden" name="user-customfields" value="<?php echo $customlist; ?>">
        <?php
        foreach ($customfields as $customkey => $customfield) {
            $optionselecta = $gateKeeper->getUserInfo($customkey);
            if (isset($customfield['type'])) { ?>
                <div class="form-group mb-3">
                    <label class="form-label"><?php echo $customfield['name']; ?></label>
                <?php
                if ($customfield['type'] === 'textarea') { ?>
                    <textarea name="<?php echo $customkey; ?>" class="form-control getuser getuser-<?php echo $customkey; ?>" rows="2">
                        <?php echo $optionselecta; ?>        
                    </textarea>
                    <?php
                }
                if ($customfield['type'] === 'select' && is_array($customfield['options'])) {
                    $multiselect = '';
                    if (isset($customfield['multiple']) && $customfield['multiple'] == true) {
                         $multiselect = ($customfield['multiple'] == true ? 'multiple="multiple"' : '');
                    } ?>
                    <select name="<?php echo $customkey; ?>" class="form-select" <?php echo $multiselect; ?>>
                    <?php
                    foreach ($customfield['options'] as $optionval => $optiontitle) {
                        $selected = ($optionselecta == $optionval) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $optionval; ?>" <?php echo $selected ;?>>
                            <?php echo $optiontitle; ?>
                        </option>
                        <?php
                    } ?>
                    </select>
                    <?php
                }
                if ($customfield['type'] === 'text' || $customfield['type'] === 'email') { ?>
                    <input type="<?php echo $customfield['type']; ?>" name="<?php echo $customkey; ?>" class="form-control" value="<?php echo $optionselecta; ?>">
                    <?php
                } ?>
                </div>
                <?php
            } // end customfield type
        } // end foreach ?>
              </div> <!-- tabpanel -->
        <?php
    } ?>
            </div><!-- tab-content -->

            <div id="profileFormActions">
            <div class="form-group mb-3">
                <label class="form-label" for="user_old_pass">
                    * <?php echo $setUp->getString("current_pass"); ?>
                </label> 
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-unlock"></i></span>
                    <input name="user_old_pass" type="password" id="oldp" required class="form-control">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> <?php print $setUp->getString("update"); ?>
                </button>
            </div>
            </div>

            </form>

            <?php if ($gateKeeper->isSuperAdmin()) { ?>
            <!-- Cloud Settings Tab Content (outside main form, uses AJAX) -->
            <div id="cloudSettingsPanel" style="display:none;">
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
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btnSaveCloudSettings">
                            <i class="bi bi-check-circle me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
            <?php } ?>
          </div> <!-- modal-body -->
        </div> <!-- modal-content -->
      </div> <!-- modal-dialog -->
    </div> <!-- modal -->
    <?php
}

/**
 * Cloud Settings JS in Update Profile (SuperAdmin only)
 */
if ($gateKeeper->isUserLoggedIn() && $gateKeeper->isSuperAdmin()) { ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cloudTab = document.querySelector('[data-bs-target="#cloudsettings"]');
        var profileActions = document.getElementById('profileFormActions');
        var cloudPanel = document.getElementById('cloudSettingsPanel');
        var btnSave = document.getElementById('btnSaveCloudSettings');
        var form = document.getElementById('cloudSettingsForm');
        var alertBox = document.getElementById('cloudSettingsAlert');

        // Toggle profile form vs cloud settings panel
        if (cloudTab && profileActions && cloudPanel) {
            document.querySelectorAll('#userpanel .nav-link').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.getAttribute('data-bs-target') === '#cloudsettings') {
                        profileActions.style.display = 'none';
                        cloudPanel.style.display = 'block';
                    } else {
                        profileActions.style.display = '';
                        cloudPanel.style.display = 'none';
                    }
                });
            });
        }

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

                fetch('admin/ajax/save-cloud-settings.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        showAlert('success', '<i class="bi bi-check-circle me-1"></i>' + data.message);
                        var brands = document.querySelectorAll('.navbar-brand');
                        brands.forEach(function(b) {
                            if (!b.querySelector('img')) b.textContent = data.appname;
                        });
                        setTimeout(function() {
                            window.location.href = data.script_url;
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
    <?php
}
