<?php

 $_CONFIG = [
  // ================== STATUS INSTALASI ==================
  'firstrun' => true, // Akan berubah jadi false otomatis setelah instalasi
  'salt' => '',       // Akan diisi otomatis oleh sistem
  'script_url' => '', // Akan diisi otomatis oleh sistem
  'session_name' => '',

  // ================== TAMPILAN & UMUM ==================
  'align_logo' => 'center',
  'appname' => 'VFM 4',
  'audio_notification' => false,
  'banner_width' => 'wide',
  'basedir' => '',
  'browser_lang' => true,
  'clipboard' => false,
  'copy_enable' => false,
  'credits' => '',
  'credits_link' => '',
  'debug_mode' => false,
  'debug_smtp' => false,
  'default_timezone' => 'UTC',
  
  // ================== MANAJEMEN FILE ==================
  'default_file_order' => 'desc',
  'default_dir_order' => 'asc',
  'delete_dir_enable' => true,
  'delete_enable' => true,
  'description' => '',
  'direct_links' => false,
  'download_dir_enable' => true,
  'download_enable_guest' => false,
  'download_enable_user' => true,
  
  // ================== EMAIL ==================
  'email_from' => '',
  'email_login' => '',
  'email_pass' => '',
  'email_logo' => false,
  'enable_prettylinks' => false,
  
  // ================== LIST & PAGINATION ==================
  'filedefnum' => 10,
  'filedeforder' => 'date',
  'fixed_navbar' => false,
  'folderdefnum' => 5,
  'folderdeforder' => 'alpha',
  'global_search' => true,
  'header_padding' => 0,
  'header_position' => 'below',
  'hide_logo' => false,
  
  // ================== KEAMANAN & FILE TERSEMBUNYI ==================
  'hidden_dirs' => ['admin'],
  'hidden_files' => [
    'index.php',
    'index.php~',
    '.htaccess',
    '.htpasswd',
    '.ftpquota',
    '.user.ini',  // Tambahan: konfigurasi PHP
    '.env',       // Tambahan: file environment
  ],
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
  
  // ================== NOTIFIKASI ==================
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
  
  // ================== REGISTRASI ==================
  'registration_enable' => false,
  'registration_lifetime' => '-1 day',
  'registration_role' => 'user',
  'registration_user_folders' => false,
  'registration_user_quota' => '',
  'rename_dir_enable' => true,
  'rename_enable' => true,
  'require_login' => true,
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
  
  // ================== UPLOAD & BLACKLIST (KRITIS) ==================
  'upload_allow_type' => false,
  'upload_email' => false,
  'upload_enable' => true,
  'upload_enable_user' => false,
  'upload_notification_enable' => false,
  
  // DAFTAR HITAM DIPERKUAT UNTUK KEAMANAN
  'upload_reject_extension' => [
    // PHP & Server Side Scripts (Wajib Blokir)
    'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'pht', 'pg', 'inc',
    // Script & Markup (Potensi XSS/Injection)
    'html', 'htm', 'xhtml', 'shtml', 'svg', 'xml', 'js',
    // Server Scripts
    'jsp', 'asp', 'aspx', 'cfm', 'pl', 'py', 'sh', 'cgi',
    // System & Config
    'ini', 'htaccess', 'htpasswd', 'bat', 'cmd', 'exe', 'msi',
  ],
  
  // ================== RECAPTCHA ==================
  'recaptcha' => false,
  'recaptcha_invisible' => true,
  'recaptcha_site' => '',
  'recaptcha_secret' => '',
  
  // ================== HAK AKSES ADMIN ==================
  'superadmin_can_appearance' => true,
  'superadmin_can_preferences' => false,
  'superadmin_can_users' => true,
  'superadmin_can_translations' => false,
  'superadmin_can_statistics' => true,
  
  // ================== KEAMANAN IP ==================
  'ip_blacklist' => false,
  'ip_whitelist' => false,
  'ip_list' => 'reject',
  'ip_redirect' => false,
  'remote_uploader' => false,
  'remote_extensions' => false,
  
  // ================== HAK AKSES ROLE ==================
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
  
  // ================== TEMA ==================
  '--color-primary' => 'hsl(216, 98%, 52%)',
  '--color-dark' => 'hsl(210, 11%, 15%)',
  '--color-light' => 'hsl(210, 16%, 98%)',
  'overwrite_files' => 'no',
  'dark_header' => true,
];