<?php
/**
 *
 * Save a new avatar image
 *
 * PHP version >= 5.3
 *
 *
 */
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
) {
    exit;
}

// Authentication check
require_once dirname(dirname(__FILE__)).'/class/class.utils.php';
require_once dirname(dirname(__FILE__)).'/class/class.setup.php';
require_once dirname(dirname(__FILE__)).'/class/class.gatekeeper.php';
$setUp = new SetUp();
$gateKeeper = new GateKeeper();
if (!$gateKeeper->isUserLoggedIn()) {
    http_response_code(403);
    exit('Unauthorized');
}

/**
 * Create a thumb from the uploaded image canvas
 * Validates that decoded data is actually an image
 *
 * @param string  $base64_string base64 string file
 * @param boolean $output_file   final file
 *
 * @return bool
 */
function base64ToJpg($base64_string, $output_file) 
{
    $data = explode(',', $base64_string);
    if (!isset($data[1])) {
        return false;
    }
    $decoded = base64_decode($data[1], true);
    if ($decoded === false) {
        return false;
    }
    // Validate decoded data is a valid image
    $imgInfo = @getimagesizefromstring($decoded);
    if ($imgInfo === false) {
        return false;
    }
    $ifp = fopen($output_file, "wb"); 
    if ($ifp === false) {
        return false;
    }
    fwrite($ifp, $decoded); 
    fclose($ifp);
    return true;
}

$relativepath = dirname(dirname(__FILE__)). '/_content/avatars';
if (!is_dir($relativepath)) {
    mkdir($relativepath, 0755);         
}

$postimg = filter_input(INPUT_POST, 'imgData', FILTER_SANITIZE_SPECIAL_CHARS);
$imgname = filter_input(INPUT_POST, 'imgName', FILTER_SANITIZE_SPECIAL_CHARS);

// Sanitize filename to prevent path traversal
$imgname = basename($imgname);
$imgname = preg_replace('/[^a-zA-Z0-9_\-]/', '', $imgname);
if (empty($imgname)) {
    exit('Invalid filename');
}

$relative = $relativepath.'/'.$imgname.'.png';

if ($postimg) {
	$finalavatar = 'admin/_content/avatars/'.$imgname.'.png';
	if (!base64ToJpg($postimg, $relative)) {
		http_response_code(400);
		exit('Invalid image data');
	}
} else {
	if (file_exists($relative)) {
		unlink($relative);
	}
	$finalavatar = false;
}
echo $finalavatar;
