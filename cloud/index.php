<?php
define('VFM_APP', true);

// 1. Optimasi Path: Gunakan __DIR__ dan definisikan variabel agar tidak repetitif
 $admin_path = __DIR__ . '/admin';

require_once $admin_path . '/include/head.php';
require_once $admin_path . '/include/activate.php';
?>
<!doctype html>
<html lang="<?php echo $setUp->lang; ?>"<?php echo $rtl_att; ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $setUp->getConfig("appname"); ?></title>
    <?php echo $setUp->printIcon("admin/_content/uploads/"); ?>
    <meta name="description" content="File Manager">
    
    <?php require $admin_path . '/include/load-css.php'; ?>
    
    <!-- CATATAN OPTIMASI: 
         Sebaiknya library jQuery dipindahkan ke bagian bawah (sebelum </body>)
         agar tidak memblokir rendering halaman. Namun, jika ada script inline 
         di tengah body yang butuh jQuery, biarkan di sini. -->
    <script type="text/javascript" src="admin/assets/jquery/jquery-3.6.1.min.js"></script>

</head>
<body id="uparea" class="<?php echo $bodyclass; ?>"<?php echo $bodydata; ?>>
    
    <div id="error"><?php echo $setUp->printAlert(); ?></div>
    <div class="overdrag"></div>

    <?php
    /**
     * ******************** HEADER ********************
     */
    // Menggunakan variabel $admin_path
    if ($setUp->getConfig('header_position') == 'above') {
        include $admin_path . $template->includeTpl('header');
        include $admin_path . $template->includeTpl('navbar');
    } else {
        include $admin_path . $template->includeTpl('navbar');
        include $admin_path . $template->includeTpl('header');
    }
    ?>

    <div class="container mb-auto pt-3">
        <div class="main-content row">
        <?php
        if ($getdownloadlist) :
            /**
             * ********* SHARED FILES DOWNLOADER *********
             */
            include $admin_path . $template->includeTpl('downloader');

        elseif ($getrp) :
            /**
             * **************** PASSWORD RESET ****************
             */
            include $admin_path . $template->includeTpl('reset');

        else :
            /**
             * **************** FILEMANAGER **************
             */
            // Kondisi: Tidak registrasi ATAU registrasi dinonaktifkan
            if (!$getreg || $setUp->getConfig('registration_enable') == false) {
                include $admin_path . '/include/user-redirect.php';
                include $admin_path . $template->includeTpl('remote-uploader');
                include $admin_path . $template->includeTpl('notify-users');
                include $admin_path . $template->includeTpl('uploadarea');
                include $admin_path . $template->includeTpl('breadcrumbs');
                include $admin_path . $template->includeTpl('list-folders');
                include $admin_path . $template->includeTpl('list-files');
                include $admin_path . $template->includeTpl('disk-space');
            }

            // Kondisi: Halaman Registrasi
            if ($getreg && $setUp->getConfig('registration_enable') == true) {
                include $admin_path . $template->includeTpl('register');
            } else {
                // Kondisi: Halaman Login
                // CATATAN LOGIKA: 
                // Jika user sudah login, apakah file 'login.php' ini tetap dimuat?
                // Jika 'login.php' hanya berisi form login, sebaiknya tambahkan pengecekan:
                // if (!$setUp->isLogged()) { include ... }
                // Namun karena saya tidak melihat logic di dalam head.php, saya biarkan struktur aslinya.
                include $admin_path . $template->includeTpl('login');
            }
        endif; 
        ?>
        </div> <!-- .main-content -->
    </div> <!-- .container -->

    <?php
    /**
     * ******************** FOOTER ********************
     */
    require $admin_path . $template->includeTpl('footer');
    require $admin_path . $template->includeTpl('modals');
    require $admin_path . '/include/load-js.php';
    ?>
</body>
</html>