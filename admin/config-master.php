<?php

 $_CONFIG = array (
  // STATUS INSTALASI
  // Ubah menjadi false jika aplikasi sudah siap digunakan di Docker
  'firstrun' => true, 
  'salt' => '', // Pastikan ini terisi otomatis saat instalasi pertama
  'script_url' => '',
  'session_name' => '',

  // TAMPILAN
  'align_logo' => 'center',
  'appname' => 'VFM 4', // Ganti dengan nama aplikasi Anda
  'audio_notification' => false,
  'banner_width' => 'wide',
  'basedir' => '',
  'browser_lang' => true,
  'clipboard' => false,
  'copy_enable' => false,
  'credits' => '',
  'credits_link' => '',
  
  // DEBUG & LOG
  'debug_mode' => false, // Pastikan FALSE di production
  'debug_smtp' => false,
  
  // PENGATURAN UMUM
  'default_timezone' => 'UTC', // Sesuaikan dengan zona waktu server (misal: Asia/Jakarta)
  'default_file_order' => 'desc',
  'default_dir_order' => 'asc',
  'delete_dir_enable' => true,
  'delete_enable' => true,
  'description' => '',
  'direct_links' => false,
  'download_dir_enable' => true,
  'download_enable_guest' => false,
  'download_enable_user' => true,
  
  // EMAIL
  'email_from' => '',
  'email_login' => '',
  'email_pass' => '',
  'email_logo' => false,
  'enable_prettylinks' => false,
  
  // TAMPILAN FILE
  'filedefnum' => 10,
  'filedeforder' => 'date',
  'fixed_navbar' => false,
  'folderdefnum' => 5,
  'folderdeforder' => 'alpha',
  'global_search' => true,
  'header_padding' => 0,
  'header_position' => 'below',
  'hide_logo' => false,
  'hidden_dirs' => 
  array (
    0 => 'admin',
  ),
  'hidden_files' => 
  array (
    1 => 'index.php',
    2 => 'index.php~',
    3 => '.htaccess',
    4 => '.htpasswd',
    5 => '.ftpquota',
    6 => '.user.ini', // Tambahan keamanan
    7 => '.env',      // Tambahan keamanan
  ),
  'hide_credits' => false,
  'inline_thumbs' => false,
  'lang' => 'en',
  'lifetime' => 1,
  'list_view' => 'list',
  'log_file' => false,
  'logo' => false,
  'logo_margin' => 0,
  'max_upload_filesize' => 0,
  'max_zip_files' => 2000,
  'max_zip_filesize' => 1024,
  'move_enable' => true,
  'navbar_logo' => false,
  'newdir_enable' => true,
  'newdir_enable_user' => false,
  'notify_download' => false,
  'notify_login' => false,
  'notify_newfolder' => false,
  'notify_upload' => false,
  'notify_registration' => false,
  'one_time_download' => false,
  'playmusic' => true,
  'playvideo' => true,
  'port' => '',
  'preloader' => 'XMLHttpRequest',
  'progress_color' => '',
  'registration_enable' => false,
  'registration_lifetime' => '-1 day',
  'registration_role' => 'user',
  'registration_user_folders' => false,
  'registration_user_quota' => '',
  'rename_dir_enable' => true,
  'rename_enable' => true,
  'require_login' => true, // Sangat disarankan TRUE
  'secure_conn' => 'none',
  'secure_sharing' => false,
  'selectivext' => 'reject',
  'sendfiles_enable' => true,
  'share_thumbnails' => true,
  'share_playmusic' => true,
  'share_playvideo' => true,
  'show_captcha' => false,
  'show_captcha_download' => false,
  'show_captcha_register' => true,
  'show_captcha_reset' => true,
  'show_folder_counter' => true,
  'show_foldertree' => true,
  'show_head' => false,
  'show_hidden_files' => false,
  'show_langmenu' => true,
  'show_langname' => false,
  'show_pagination' => true,
  'show_pagination_folders' => false,
  'show_pagination_num' => true,
  'show_pagination_num_folder' => false,
  'show_path' => true,
  'show_percentage' => false,
  'show_search' => true,
  'show_usermenu' => true,
  'single_progress' => false,
  'smtp_auth' => false,
  'smtp_enable' => false,
  'smtp_server' => '',
  'starting_dir' => './uploads/',
  'sticky_alerts' => true,
  'sticky_alerts_pos' => 'top-right',
  'thumbnails' => true,
  'thumbnails_height' => 800,
  'thumbnails_width' => 760,
  'time_format' => 'd/m/Y - H:i',
  'txt_direction' => 'LTR',
  'upload_allow_type' => false,
  'upload_email' => false,
  'upload_enable' => true,
  'upload_enable_user' => false,
  'upload_notification_enable' => false,
  
  // KEAMANAN: BLACKLIST EKSTENSI (OPTIMALKAN)
  // Menambahkan varian PHP, Script, dan File Berbahaya lainnya
  'upload_reject_extension' => 
  array (
    // PHP & Script Server Side
    0 => 'php',
    1 => 'php3',
    2 => 'php4',
    3 => 'php5',
    4 => 'php7',
    5 => 'phtml',
    6 => 'phar',
    7 => 'pht',
    8 => 'pg',
    9 => 'inc',
    
    // Script & Markup Berbahaya (XSS/Execution)
    10 => 'html',
    11 => 'htm',
    12 => 'xhtml',
    13 => 'shtml',
    14 => 'svg',    // Berpotensi XSS
    15 => 'xml',    // Berpotensi XXE
    16 => 'js',     // JavaScript
    17 => 'jsp',
    18 => 'asp',
    19 => 'aspx',
    20 => 'cfm',
    21 => 'pl',
    22 => 'py',
    23 => 'sh',
    24 => 'cgi',
    
    // Konfigurasi & System
    25 => 'ini',
    26 => 'htaccess',
    27 => 'htpasswd',
    28 => 'bat',
    29 => 'cmd',
    30 => 'exe',
    31 => 'msi',
  ),
  
  // RECAPTCHA
  'recaptcha' => false,
  'recaptcha_invisible' => true,
  'recaptcha_site' => '',
  'recaptcha_secret' => '',
  
  // HAK AKSES ADMIN
  'superadmin_can_appearance' => true,
  'superadmin_can_preferences' => false,
  'superadmin_can_users' => true,
  'superadmin_can_translations' => false,
  'superadmin_can_statistics' => true,
  
  // KEAMANAN IP
  'ip_blacklist' => false,
  'ip_whitelist' => false,
  'ip_list' => 'reject',
  'ip_redirect' => false,
  
  // REMOTE UPLOAD
  'remote_uploader' => false,
  'remote_extensions' => false,
  
  // HAK AKSES ROLE
  'view_enable_guest' => true,
  'view_enable_user' => true,
  'viewdirs_enable_guest' => false,
  'viewdirs_enable_user' => true,
  'sendfiles_enable_guest' => false,
  'sendfiles_enable_user' => false,
  'sendfiles_enable_editor' => true,
  'upload_enable_editor' => true,
  'delete_enable_editor' => false,
  'rename_enable_editor' => true,
  'move_enable_editor' => true,
  'copy_enable_editor' => false,
  'newdir_enable_editor' => true,
  'delete_dir_enable_editor' => false,
  'rename_dir_enable_editor' => true,
  'sendfiles_enable_contributor' => true,
  'upload_enable_contributor' => true,
  'delete_enable_contributor' => false,
  'rename_enable_contributor' => false,
  'move_enable_contributor' => false,
  'copy_enable_contributor' => false,
  'newdir_enable_contributor' => true,
  'delete_dir_enable_contributor' => false,
  'rename_dir_enable_contributor' => false,
  
  // TEMA
  '--color-primary' => 'hsl(216, 98%, 52%)',
  '--color-dark' => 'hsl(210, 11%, 15%)',
  '--color-light' => 'hsl(210, 16%, 98%)',
  'overwrite_files' => 'no',
  'dark_header' => true,
);