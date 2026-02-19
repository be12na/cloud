<?php
/**
 * VFM Downloader - Optimized & Secured
 * PHP version >= 5.3
 */

// 1. Inisialisasi & Konfigurasi
require_once __DIR__ . '/class/class.setup.php';
require_once __DIR__ . '/class/class.gatekeeper.php';
require_once __DIR__ . '/class/class.downloader.php';
require_once __DIR__ . '/class/class.utils.php';
require_once __DIR__ . '/class/class.logger.php';

 $setUp = new SetUp();
 $gateKeeper = new GateKeeper();
 $downloader = new Downloader();
 $logger = new Logger();

if ($setUp->getConfig('debug_mode') === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Cek IP Blacklist
Utils::checkIP();

// Set Timezone
 $timeconfig = $setUp->getConfig('default_timezone');
 $timezone = !empty($timeconfig) ? $timeconfig : "UTC";
date_default_timezone_set($timezone);

 $script_url = $setUp->getConfig('script_url');

// Sanitasi Input
 $getzip = filter_input(INPUT_GET, 'zip', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 $getfile = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 $hash = filter_input(INPUT_GET, 'h', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 $supah = filter_input(INPUT_GET, 'sh', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 $json_file = filter_input(INPUT_GET, 'share', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

 $alt = $setUp->getConfig('salt');
 $altone = $setUp->getConfig('session_name');

// Deteksi Android
 $android = (stripos(strtolower($_SERVER['HTTP_USER_AGENT'] ?? ''), 'android') !== false);

/**
 * 1. DOWNLOAD SINGLE FILE (Shared Link / Non-logged)
 */
if ($json_file && is_numeric($getfile)) {
    $filenum = $getfile;
    
    // SECURITY FIX: Gunakan basename untuk mencegah Path Traversal
    $share_json = __DIR__ . '/_content/share/' . basename($json_file) . '.json';

    if (file_exists($share_json)) {
        $datarray = json_decode(file_get_contents($share_json), true);
        $time = $datarray['time'];
        $hash_share = $datarray['hash'];

        // Verifikasi Hash
        if (md5($time . $hash_share) !== $supah) {
            Utils::setError('<i class="bi bi-slash-circle"></i> ' . $setUp->getString("access_denied"));
            header('Location: ' . $script_url);
            exit;
        }

        // Cek Waktu Kadaluarsa
        if ($downloader->checkTime($time) == true) {
            $pieces = explode(",", $datarray['attachments']);

            // One Time Download: Hapus file JSON jika kosong
            if (!count($pieces)) {
                unlink($share_json);
            }

            if (isset($pieces[$filenum])) {
                $getfile = $pieces[$filenum];

                if ($downloader->checkFile($getfile) == true) {
                    $headers = $downloader->getHeaders($getfile);

                    // One Time Download: Hapus item dari JSON
                    if ($setUp->getConfig('one_time_download')) {
                        unset($pieces[$filenum]);
                        $datarray['attachments'] = implode(',', $pieces);
                        // LOCK_EX untuk mencegah race condition
                        file_put_contents($share_json, json_encode($datarray), LOCK_EX);
                    }

                    // Force download for images/videos (skip direct_links redirect)
                    $force_download = $downloader->isMediaFile($headers['filename']);

                    if ($setUp->getConfig('direct_links') && !$force_download) {
                        if ($headers['content_type'] == 'audio/mp3') {
                            $logger->logPlay($headers['trackfile']);
                        } else {
                            $logger->logDownload($headers['trackfile']);
                        }
                        header('Location: ' . $script_url . base64_decode($getfile));
                        exit;
                    }

                    // Force attachment disposition for media files
                    if ($force_download) {
                        $headers['disposition'] = 'attachment';
                        $headers['content_type'] = 'application/octet-stream';
                    }

                    if ($downloader->download(
                        $headers['file'], 
                        $headers['filename'], 
                        $headers['file_size'], 
                        $headers['content_type'],
                        $headers['disposition'],
                        $android 
                    ) === true) {
                        $logger->logDownload($headers['trackfile']);
                    }
                    exit;
                }
            }
        }
    }
}

/**
 * 2. DOWNLOAD SINGLE FILE (Logged In Users)
 */
if ($getfile && $hash
    && $downloader->checkFile($getfile) == true
    && md5($alt . $getfile . $altone . $alt) === $hash
) {
    $playmp3 = filter_input(INPUT_GET, 'audio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $headers = $downloader->getHeaders($getfile, $playmp3);

    if (($gateKeeper->isUserLoggedIn() && $downloader->subDir($headers['dirname']) == true) 
        || $gateKeeper->isLoginRequired() == false
    ) {
        // Force download for images/videos (skip direct_links redirect)
        $force_download = $downloader->isMediaFile($headers['filename']);

        if ($setUp->getConfig('direct_links') && !$force_download) {
            if ($headers['content_type'] == 'audio/mp3') {
                $logger->logPlay($headers['trackfile']);
            } else {
                $logger->logDownload($headers['trackfile']);
            }
            header('Location: ' . $script_url . base64_decode($getfile));
            exit;
        }

        if ($headers['content_type'] == 'audio/mp3') {
            $logger->logPlay($headers['trackfile']);
        }

        // Force attachment disposition for media files
        if ($force_download) {
            $headers['disposition'] = 'attachment';
            $headers['content_type'] = 'application/octet-stream';
        }

        if ($downloader->download(
            $headers['file'],
            $headers['filename'],
            $headers['file_size'],
            $headers['content_type'],
            $headers['disposition'],
            $android
        ) === true) {
            if ($headers['content_type'] !== 'audio/mp3') {
                $logger->logDownload($headers['trackfile']);
            }
        }
        exit;
    }
    
    Utils::setError('<i class="bi bi-slash-circle"></i> ' . $setUp->getString("access_denied"));
    header('Location: ' . $script_url);
    exit;
}

/**
 * 3. DOWNLOAD ZIP
 */
if ($getzip) {
    $supahzip = filter_input(INPUT_GET, 'n', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // SECURITY FIX: Gunakan basename untuk mencegah Path Traversal
    $zip_json = __DIR__ . '/tmp/' . basename($getzip) . '.json';

    if (file_exists($zip_json)) {
        $datarray = json_decode(file_get_contents($zip_json), true);
        $time = $datarray['time'];
        $hash_zip = $datarray['hash'];
        $folder = $datarray['dir'];
        $files = $datarray['files'];

        if (md5($time . $hash_zip) !== $supahzip) {
            Utils::setError('<i class="bi bi-slash-circle"></i> ' . $setUp->getString("access_denied"));
            header('Location: ' . $script_url);
            exit;
        }

        if ($folder || $files) {
            @set_time_limit(0);
            session_write_close(); // Bebaskan session lock untuk download besar
            
            include __DIR__ . '/assets/zipstream/autoload.php';
            $cleanpath = dirname(__DIR__) . '/' . ltrim($setUp->getConfig('starting_dir'), './');
        }

        // ZIP FOLDER
        if ($folder) {
            $folderpathinfo = Utils::mbPathinfo($cleanpath . $folder);
            $archivename = Utils::checkMagicQuotes($folderpathinfo['filename']);
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            $zip = new \PHPZip\Zip\Stream\ZipStream($archivename . '.zip');
            $zip->addDirectoryContent($cleanpath . $folder, $archivename);
            $zip->finalize();
            $logger->logDownload($folder, true);
        }

        // ZIP FILES
        if ($files) {
            $archivename = 'zip-' . $time;
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            $zip = new \PHPZip\Zip\Stream\ZipStream($archivename . '.zip');

            foreach ($files as $file) {
                $zip->addLargeFile($cleanpath . $file, $archivename . '/' . basename($file), filemtime($cleanpath . $file));
            }
            $zip->finalize();
            $logger->logDownload($files);
        }

        unlink($zip_json);
        exit;
    }
}

// Fallback Error
Utils::setError($setUp->getString("link_expired"));
header('Location: ' . $script_url);
exit;