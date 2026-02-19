<?php
/**
 * Display images (icons and thumbnails)
 *
 *
 */
if (!class_exists('ImageServer', false)) {
    /**
     * Class ImageServer
     *
 *
     */
    class ImageServer
    {
        /**
         * Checks if an image is requested and displays one if needed
         *
         * @return true/false
         */
        public static function showImage()
        {
            $setUp = SetUp::getInstance();
            $thumb = filter_input(INPUT_GET, 'thumb', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($thumb) {
                $inline = (isset($_GET['in']) ? true : false);
                if (strlen($thumb) > 0
                    && ($setUp->getConfig('thumbnails') == true
                    || $setUp->getConfig('inline_thumbs') == true)
                ) {
                    ImageServer::showThumbnail(base64_decode($thumb), $inline);
                }
                return true;
            }
            return false;
        }

        /**
         * Checks if isEnabledPdf()
         *
         * @return true/false
         */
        public static function isEnabledPdf()
        {
            if (class_exists('Imagick')) {
                return true;
            }
            return false;
        }

        /**
         * Checks if ffmpeg is available for video thumbnails
         *
         * @return string|false path to ffmpeg or false
         */
        public static function isEnabledFfmpeg()
        {
            static $ffmpegPath = null;
            if ($ffmpegPath !== null) {
                return $ffmpegPath;
            }

            $setUp = SetUp::getInstance();
            $configPath = $setUp->getConfig('ffmpeg_path');

            // Try configured path first
            if ($configPath && $configPath !== 'ffmpeg') {
                $configPath = str_replace('\\', '/', $configPath);
                if (is_executable($configPath)) {
                    $ffmpegPath = $configPath;
                    return $ffmpegPath;
                }
            }

            // Try auto-detect
            $output = array();
            $returnVar = -1;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                @exec('where ffmpeg 2>NUL', $output, $returnVar);
            } else {
                @exec('which ffmpeg 2>/dev/null', $output, $returnVar);
            }

            if ($returnVar === 0 && !empty($output[0])) {
                $ffmpegPath = trim($output[0]);
                return $ffmpegPath;
            }

            // Try common paths
            $commonPaths = array(
                '/usr/bin/ffmpeg',
                '/usr/local/bin/ffmpeg',
                '/opt/bin/ffmpeg',
                '/opt/local/bin/ffmpeg',
            );
            foreach ($commonPaths as $path) {
                if (is_executable($path)) {
                    $ffmpegPath = $path;
                    return $ffmpegPath;
                }
            }

            $ffmpegPath = false;
            return $ffmpegPath;
        }

        /**
         * Create thumbnail from video file using ffmpeg
         *
         * @param string  $file   video file path
         * @param boolean $inline inline thumb or zoom
         *
         * @return resource|false GD image resource or false
         */
        public static function createVideoThumbnail($file, $inline = false)
        {
            $ffmpeg = ImageServer::isEnabledFfmpeg();
            if (!$ffmpeg) {
                return false;
            }

            $setUp = SetUp::getInstance();
            $relative = dirname(dirname(dirname(__FILE__))).'/';
            $file = urldecode($file);
            $filepath = $relative.$file;

            if (!file_exists($filepath)) {
                return false;
            }

            // Create temp file for the extracted frame
            $tempFile = tempnam(sys_get_temp_dir(), 'vfm_vid_');
            $tempJpg = $tempFile . '.jpg';

            // Extract frame at 1 second (or first frame if video is shorter)
            $cmd = escapeshellarg($ffmpeg)
                . ' -i ' . escapeshellarg($filepath)
                . ' -ss 00:00:01 -vframes 1 -f image2'
                . ' -y ' . escapeshellarg($tempJpg)
                . ' 2>&1';

            $output = array();
            $returnVar = -1;
            @exec($cmd, $output, $returnVar);

            // If 1 second failed, try first frame
            if ($returnVar !== 0 || !file_exists($tempJpg) || filesize($tempJpg) === 0) {
                $cmd = escapeshellarg($ffmpeg)
                    . ' -i ' . escapeshellarg($filepath)
                    . ' -vframes 1 -f image2'
                    . ' -y ' . escapeshellarg($tempJpg)
                    . ' 2>&1';
                @exec($cmd, $output, $returnVar);
            }

            // Clean up temp file without extension
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if ($returnVar !== 0 || !file_exists($tempJpg) || filesize($tempJpg) === 0) {
                if (file_exists($tempJpg)) {
                    @unlink($tempJpg);
                }
                return false;
            }

            // Load the extracted frame and create thumbnail
            $image = @imagecreatefromjpeg($tempJpg);
            @unlink($tempJpg);

            if (!$image) {
                return false;
            }

            if ($inline == true) {
                $thumbsize = 420;
                $max_width = $thumbsize;
                $max_height = $thumbsize;
            } else {
                $max_width = intval($setUp->getConfig('thumbnails_width'));
                $max_height = intval($setUp->getConfig('thumbnails_height'));
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $new_width = intval($max_width);
            $new_height = intval($max_height);

            if ($inline == true) {
                // crop to square thumbnail
                if ($width > $height) {
                    $y = 0;
                    $x = intval(($width - $height) / 2);
                    $smallestSide = $height;
                } else {
                    $x = 0;
                    $y = intval(($height - $width) / 2);
                    $smallestSide = $width;
                }
                $thumb = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($thumb, $image, 0, 0, $x, $y, $new_width, $new_height, $smallestSide, $smallestSide);
            } else {
                // resize maintaining aspect ratio
                if (($width/$height) > ($new_width/$new_height)) {
                    $new_height = $new_width * ($height / $width);
                } else {
                    $new_width = $new_height * ($width / $height);
                }
                $new_width = intval($new_width >= $width ? $width : $new_width);
                $new_height = intval($new_height >= $height ? $height : $new_height);

                $thumb = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            }

            // Add play button overlay for inline thumbnails
            if ($inline == true) {
                ImageServer::addPlayOverlay($thumb, $new_width, $new_height);
            }

            imagedestroy($image);
            return $thumb;
        }

        /**
         * Add a semi-transparent play button overlay to a video thumbnail
         *
         * @param resource $image  GD image resource
         * @param int      $width  image width
         * @param int      $height image height
         *
         * @return void
         */
        public static function addPlayOverlay(&$image, $width, $height)
        {
            $cx = intval($width / 2);
            $cy = intval($height / 2);
            $radius = intval(min($width, $height) / 6);

            // Semi-transparent dark circle
            $overlay = imagecreatetruecolor($width, $height);
            imagealphablending($overlay, true);
            imagesavealpha($overlay, true);
            $transparent = imagecolorallocatealpha($overlay, 0, 0, 0, 127);
            imagefill($overlay, 0, 0, $transparent);

            $circleColor = imagecolorallocatealpha($overlay, 0, 0, 0, 60);
            imagefilledellipse($overlay, $cx, $cy, $radius * 2, $radius * 2, $circleColor);

            // Play triangle
            $triColor = imagecolorallocatealpha($overlay, 255, 255, 255, 30);
            $triSize = intval($radius * 0.6);
            $points = array(
                $cx - intval($triSize * 0.4), $cy - $triSize,
                $cx - intval($triSize * 0.4), $cy + $triSize,
                $cx + intval($triSize * 0.9), $cy,
            );
            imagefilledpolygon($overlay, $points, 3, $triColor);

            imagecopy($image, $overlay, 0, 0, 0, 0, $width, $height);
            imagedestroy($overlay);
        }

        /**
         * Preapre PDF for thumbnail
         *
         * @param string $file the file to convert
         *
         * @return false | $image | default pdf placeholder if imagemagick fails (usually with pasword protected pdfs)
         */
        public static function openPdf($file)
        {
            if (!ImageServer::isEnabledPdf()) {
                return false;
            }
            $file = urldecode($file);
            
            try {
                $img = new Imagick($file.'[0]');
            } catch (ImagickException $e) {
                // echo 'Caught exception: ', $e->getMessage(), "\n";
                // var_dump($e);
                unset($e);
                $image = imagecreatefromjpeg('admin/images/placeholder-pdf.jpg');
                return $image;
            }

            $img->setImageFormat('png');

            try {
                $str = $img->getImageBlob();
                $image = imagecreatefromstring($str);
            } catch (ImagickException $e) {
                // echo 'Caught exception: ', $e->getMessage(), "\n";
                // var_dump($e);
                unset($e);
                $image = imagecreatefromjpeg('admin/images/placeholder-pdf.jpg');
            }
            return $image;
        }

        /**
         * Creates and returns a thumbnail image object from an image file
         *
         * @param string  $file   file to convert
         * @param boolean $inline thumbs or zoom
         *
         * @return null | $new_image
         */
        public static function createThumbnail($file, $inline = false)
        {
            $setUp = SetUp::getInstance();
            $relative = dirname(dirname(dirname(__FILE__))).'/';
            $file = urldecode($file);
            $filepath = $relative.$file;
            $imageInfo = false;
            $ext = strtolower(Utils::getFileExtension($file));

            if ($inline == true) {
                // $thumbsize = $setUp->getConfig('inline_tw');
                $thumbsize = 420;
                $max_width = $thumbsize;
                $max_height = $thumbsize;
            } else {
                $max_width = intval($setUp->getConfig('thumbnails_width'));
                $max_height = intval($setUp->getConfig('thumbnails_height'));
            }
            
            $videoExts = array('mp4', 'webm', 'ogg', 'ogv');
            if ($ext == 'pdf') {
                $image = ImageServer::openPdf($filepath);
            } elseif (in_array($ext, $videoExts)) {
                return ImageServer::createVideoThumbnail($file, $inline);
            } else {
                $imageInfo = getimagesize($filepath);
                $image = ImageServer::openImage($filepath, $imageInfo, $inline);
            }
            if ($image == false) {
                return false;
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $new_width = intval($max_width);
            $new_height = intval($max_height);

            // set background color for transparent images
            $bgR = 240;
            $bgG = 240;
            $bgB = 240;

            if ($inline == true) {
                // crop to square thumbnail
                if ($width > $height) {
                    $y = 0;
                    $x = intval(($width - $height) / 2);
                    $smallestSide = $height;
                } else {
                    $x = 0;
                    $y = intval(($height - $width) / 2);
                    $smallestSide = $width;
                }
                $thumb = imagecreatetruecolor($new_width, $new_height);
                $bgcolor = imagecolorallocate($thumb, $bgR, $bgG, $bgB);
                imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $bgcolor);
                imagecopyresampled($thumb, $image, 0, 0, $x, $y, $new_width, $new_height, $smallestSide, $smallestSide);
            } else {
                // resize mantaining aspect ratio
                if (($width/$height) > ($new_width/$new_height)) {
                    $new_height = $new_width * ($height / $width);
                } else {
                    $new_width = $new_height * ($width / $height);
                }
                $new_width = intval($new_width >= $width ? $width : $new_width);
                $new_height = intval($new_height >= $height ? $height : $new_height);

                $thumb = imagecreatetruecolor($new_width, $new_height);
                $bgcolor = imagecolorallocate($thumb, $bgR, $bgG, $bgB);
                imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $bgcolor);
                imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            }

            // Rotate image if is jpeg with exif data
            if ($imageInfo && function_exists('exif_read_data')) {
                if (isset($imageInfo['mime']) && $imageInfo['mime'] == 'image/jpeg') {
                    $exif = @exif_read_data($filepath);
                    if ($exif) {
                        $thumb = ImageServer::correctRotation($thumb, $exif);
                    }
                }
            }
            return $thumb;
        }

        /**
         * Function for displaying the thumbnail.
         * Includes attempts at cacheing it so that generation is minimised.
         *
         * @param string  $file   file to convert
         * @param boolean $inline thumbs or zoom
         *
         * @return $image
         */
        public static function showThumbnail($file, $inline = false)
        {
            $setUp = SetUp::getInstance();
            $relative = dirname(dirname(__FILE__)).'/';
            $thumbsdir = $relative.'_content/thumbs';

            if (!is_dir($thumbsdir)) {
                if (!mkdir($thumbsdir, 0755)) {
                    Utils::setError('error creating '.$thumbsdir.' directory');
                    return false;
                }
            }

            // $relativereal = $inline ? '../../' : '';
            $realfile = urldecode($file);
            // $filepath = $relativereal.$realfile;

            $filepath = dirname(dirname(dirname(__FILE__))).'/'.$realfile;

            $filemtime = filemtime($filepath);
            $filetime = $filemtime ? $filemtime : 'no-data';
            $md5name = md5($file.$filetime);
            $thumbname = $inline ? $md5name.'.jpg' : $md5name.'-big.jpg';
            $thumbpath = $thumbsdir.'/'.$thumbname;

            if (!file_exists($thumbpath)) {
                $file = Utils::extraChars($file);
                $image = ImageServer::createThumbnail($file, $inline);

                $imageout = $image ? $image : imagecreatefromjpeg($relative.'images/placeholder.jpg');

                if ($imageout) {
                    imagejpeg($imageout, $thumbpath, 80);
                    imagedestroy($imageout);
                }
            }
            if ($inline) {
                return 'admin/_content/thumbs/'.$thumbname;
            } else {
                // header('Location: '.$thumbpath);
                header('Location: '.$setUp->getConfig('script_url').'admin/_content/thumbs/'.$thumbname);
                exit;
            }
        }

        /**
         * Open different types of image files
         *
         * @param string $file      the file to convert
         * @param array  $imageInfo getimagesize array
         * @param bool   $inline    inline thumb
         *
         * @return $img
         */
        public static function openImage($file, $imageInfo = false, $inline = false)
        {
            // $relative = $inline ? '../' : 'admin/';
            $relative = dirname(dirname(__FILE__)).'/';
            if (!$imageInfo) {
                return false;
            }

            $channels = isset($imageInfo['channels']) && $imageInfo['channels'] > 0 ? $imageInfo['channels'] : 4;
            $bits = isset($imageInfo['bits']) && $imageInfo['bits'] > 0 ? $imageInfo['bits'] : 8;

            // Check the memory needed
            // $memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $bits * $channels / 8 + pow(2, 16)) * 2);
            // Old method
            $memoryNeeded = ($imageInfo[0] * $imageInfo[1] * $bits * $channels);

            $memory_get_limit = ini_get('memory_limit');

            $memoryLimit = strlen($memory_get_limit) > 0 ? ImageServer::returnBytes($memory_get_limit) : false;
            $lowmemory = false;

            $memory_usage = 0;
            if (function_exists('memory_get_usage')) {
                $memory_usage = memory_get_usage();
            }

            $realneeded = ($memoryNeeded+$memory_usage);

            // Try to set the needed memory_limit
            if ($memoryLimit && $realneeded > $memoryLimit) {
                $lowmemory = true;
                $formatneeded = (round(($memoryNeeded+$memory_usage)/1024/1024)+10).'M';
                if (ini_set('memory_limit', $formatneeded)) {
                    $lowmemory = false;
                }
            }

            $img = false;

            // Genereate thumbs
            if (!$lowmemory) {
                switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $img = imagecreatefromjpeg($file);
                    break;
                case 'image/gif':
                    $img = imagecreatefromgif($file);
                    break;
                case 'image/png':
                    $img = imagecreatefrompng($file);
                    break;
                default:
                    $img = imagecreatefromstring(file_get_contents($file));
                    break;
                }
                if (!$img) {
                    $img = imagecreatefromstring(file_get_contents($file));
                }
            }
            if (!$img) {
                $img = imagecreatefromjpeg($relative.'images/placeholder.jpg');
            }
            return $img;
        }

        /**
         * Adjust image rotation
         *
         * @param obj   $imageResource the image to rotate
         * @param array $exif          the exif data
         *
         * @return converted size
         */
        public static function correctRotation($imageResource, $exif = false) 
        {
            $image = $imageResource;

            $rotate = false;
            $flip = false;

            if ($exif && function_exists('imagerotate') && !empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                case 3: // 180
                case 4: // 180 + flip horiz
                    $rotate = 180;
                    break;
                case 5: // -90 + flip horiz
                case 6: // -90
                    $rotate = 270;
                    break;
                case 7: // 90 + flip horiz
                case 8: // 90
                    $rotate = 90;
                    break;
                default:
                    $rotate = false;
                }

                if (function_exists('imageflip')) {
                    switch ($exif['Orientation']) {
                    case 2: // flip horiz
                    case 4: // 180 + flip horiz
                    case 5: // -90 + flip horiz
                    case 7: // 90 + flip horiz
                        $flip = true;
                        break;
                    }
                }

                if ($rotate) {
                    try {
                        $image = imagerotate($imageResource, $rotate, 0);
                    } catch (Exception $e) {
                        unset($e);
                    }
                }

                if ($flip === true) {
                    try {
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                    } catch (Exception $e) {
                        unset($e);
                    }
                }
            }
            return $image;
        }

        /**
         * Convert M K G in bytes
         *
         * @param string $size_str original size
         *
         * @return converted size
         */
        public static function returnBytes($size_str)
        {
            switch (substr($size_str, -1)) {
            case 'M':
            case 'm':
                return (int)$size_str * 1048576;
            case 'K':
            case 'k':
                return (int)$size_str * 1024;
            case 'G':
            case 'g':
                return (int)$size_str * 1073741824;
            default:
                return $size_str;
            }
        }
    }
}
