<?php
/**
 * Lgging user activity
 *
 *
 */
if (!class_exists('Logger', false)) {
    /**
     * Class Logger
     *
 *
     */
    class Logger
    {
        /**
         * Print log file
         *
         * @param string $message the message to log
         * @param string $relpath relative path of log file // DROPPED in favor of dirname()
         *
         * @return $message
         */
        public static function log($message, $relpath = 'admin/')
        {
            $setUp = SetUp::getInstance();
            if ($setUp->getConfig('log_file') == true) {
                $logjson = dirname(dirname(__FILE__)).'/_content/log/'.date('Y-m-d').'.json';

                if (Utils::isFileWritable($logjson)) {
                    $message['time'] = date('H:i:s');
                    if (file_exists($logjson)) {
                        $oldlog = json_decode(file_get_contents($logjson), true);
                    } else {
                        $oldlog = array();
                    }

                    $daily = date('Y-m-d');
                    $oldlog[$daily][] = $message;

                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        file_put_contents($logjson, json_encode($oldlog, JSON_FORCE_OBJECT), LOCK_EX);
                    } else {
                        $f = fopen($logjson, 'c');
                        if ($f && flock($f, LOCK_EX | LOCK_NB)) {
                            ftruncate($f, 0);
                            fwrite($f, json_encode($oldlog, JSON_FORCE_OBJECT));
                            fflush($f);
                            flock($f, LOCK_UN);
                        }
                        if ($f) {
                            fclose($f);
                        }
                    }
                } else {
                    Utils::setError('The script does not have permissions to write inside "/_content/log/" folder. check CHMOD'.$logjson);
                    return;
                }
            }
        }

        /**
         * Log user login
         *
         * @return $message
         */
        public static function logAccess()
        {
            $gateKeeper = GateKeeper::getInstance();
            $setUp = SetUp::getInstance();
            $message = array(
                'user' => $gateKeeper->getUserInfo('name'),
                'action' => 'log_in',
                'type' => '',
                'item' => 'IP: '.Logger::getClientIP(),
            );
            Logger::log($message);
            if ($setUp->getConfig('notify_login')) {
                Logger::emailNotification('--', 'login');
            }
        }

        /**
         * Get user IP
         *
         * @return $ipaddress
         */
        public static function getClientIP()
        {
            // Only trust REMOTE_ADDR for security decisions
            // HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR are client-controlled and spoofable
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $ip = 'UNKNOWN';
            }
            // Append forwarded-for info for logging only (clearly labeled)
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwarded = filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_SANITIZE_SPECIAL_CHARS);
                $ip .= ' (forwarded-for: '.$forwarded.')';
            }
            return $ip;
        }

        /**
         * Log user creation of folders and files
         *
         * @param string $path  the path to set
         * @param string $isDir may be 'dir' or 'file'
         *
         * @return $message
         */
        public static function logCreation($path, $isDir)
        {
            $gateKeeper = GateKeeper::getInstance();
            $setUp = SetUp::getInstance();
            $path = addslashes($path);
            $message = array(
                'user' => $gateKeeper->getUserInfo('name'),
                'action' => 'ADD',
                'type' => $isDir ? 'folder':'file',
                'item' => ltrim($path, './'),
            );
            Logger::log($message);
            // if (!$isDir && $setUp->getConfig('notify_upload')) {
            //     Logger::emailNotification($path, 'upload');
            // }
            if ($isDir && $setUp->getConfig('notify_newfolder')) {
                Logger::emailNotification($path, 'newdir');
            }
        }

        /**
         * Log user deletion of folders and files
         *
         * @param string  $path   the path to set
         * @param boolean $isDir  file or directory
         * @param boolean $remote true if called inside admin
         *
         * @return $message
         */
        public static function logDeletion($path, $isDir, $remote = false)
        {
            $gateKeeper = GateKeeper::getInstance();
            $path = addslashes($path);
            $message = array(
                'user' => $gateKeeper->getUserInfo('name'),
                'action' => 'REMOVE',
                'type' => $isDir ? 'folder':'file',
                'item' => ltrim($path, './'),
            );
            Logger::log($message);
        }
        
        /**
         * Log download of single files
         *
         * @param string $path     the path to set
         * @param bool   $folder   if is folder
         * @param string $relative relative path to /log/ folder
         *
         * @return $message
         */
        public static function logDownload($path, $folder = false, $relative = '')
        {
            $gateKeeper = GateKeeper::getInstance();
            $setUp = SetUp::getInstance();
            $user = $gateKeeper->getUserName();
            $mailmessage = '';
            $type = $folder ? 'folder' : 'file';
            if (is_array($path)) {
                foreach ($path as $value) {
                    $path = addslashes($value);
                    $message = array(
                        'user' => $user,
                        'action' => 'DOWNLOAD',
                        'type' => $type,
                        'item' => ltrim($path, './'),
                    );
                    $mailmessage .= $path."\n";
                    Logger::log($message, $relative);
                }
            } else {
                $path = addslashes($path);
                $message = array(
                    'user' => $user,
                    'action' => 'DOWNLOAD',
                    'type' => $type,
                    'item' => ltrim($path, './'),
                );
                $mailmessage = $path;
                Logger::log($message, $relative);
            }
            if ($setUp->getConfig('notify_download')) {
                Logger::emailNotification($mailmessage, 'download');
            }
        }

        /**
         * Log play of single track
         *
         * @param string $path the path to set
         *
         * @return $message
         */
        public static function logPlay($path)
        {
            $gateKeeper = GateKeeper::getInstance();
            $path = addslashes($path);
            $message = array(
                'user' =>  $gateKeeper->getUserInfo('name') ? $gateKeeper->getUserInfo('name') : '--',
                'action' => 'PLAY',
                'type' => 'file',
                'item' => ltrim($path, './'),
            );
            Logger::log($message, '');
        }

        /**
         * Send email notfications for activity logs
         *
         * @param string $path   the path to set
         * @param string $action can be 'download' | 'upload' | 'newdir' | 'login'
         *
         * @return $message
         */
        public static function emailNotification($path, $action = false)
        {
            $setUp = SetUp::getInstance();
            $gateKeeper = GateKeeper::getInstance();

            if (strlen($setUp->getConfig('upload_email')) > 5) {

                $time = $setUp->formatModTime(time());
                $appname = $setUp->getConfig('appname');
                switch ($action) {
                case 'download':
                    $title = $setUp->getString('new_download');
                    break;
                case 'upload':
                    $title = $setUp->getString('new_upload');
                    break;
                case 'newdir':
                    $title = $setUp->getString('new_directory');
                    break;
                case 'login':
                    $title = $setUp->getString('new_access');
                    break;
                default:
                    $title = $setUp->getString('new_activity');
                    break;
                }
                $message = $time."\n\n";
                $message .= "IP : ".Logger::getClientIP()."\n";
                $message .= $setUp->getString('user')." : ".$gateKeeper->getUserInfo('name')."\n";
                $message .= $setUp->getString('path')." : ".$path."\n";
         
                $sendTo = $setUp->getConfig('upload_email');
                $from = "=?UTF-8?B?".base64_encode($appname)."?=";
                $server_name = preg_replace('/[^a-zA-Z0-9.-]/', '', $_SERVER['SERVER_NAME'] ?? 'localhost');
                mail(
                    $sendTo,
                    "=?UTF-8?B?".base64_encode($title)."?=",
                    $message,
                    "Content-type: text/plain; charset=UTF-8\r\n".
                    "From: ".$from." <noreply@{$server_name}>\r\n".
                    "Reply-To: ".$from." <noreply@{$server_name}>"
                );
            }
        }
    }
}
