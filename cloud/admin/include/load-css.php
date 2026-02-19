<?php
if (!defined('VFM_APP')) {
    return;
}
?>
<link rel="stylesheet" href="admin/assets/bootstrap/css/bootstrap<?php echo $rtl_ext; ?>.min.css?v=5.1">
<link rel="stylesheet" href="admin/icons/bootstrap-icons.min.css?v=5.1">
<?php
if ($setUp->getConfig('debug_mode')) {
    ?>
    <link rel="stylesheet" href="admin/assets/datatables/datatables.min.css?v=1.10.16">
    <link rel="stylesheet" href="admin/assets/plyr/plyr.css?v=3.7.2">
    <link rel="stylesheet" href="admin/assets/vfm/css/vfm-style.css?v=<?php echo VFM_VERSION; ?>">
    <?php
} else {
    ?>
    <link rel="stylesheet" href="admin/css/vfm-bundle.min.css?v=<?php echo VFM_VERSION; ?>">
    <?php
}
$colors_css = file_exists('admin/_content/template/colors.css') ? 'admin/_content/template/colors.css' : 'admin/css/colors.css';
$custom_css = 'admin/_content/template/style.css';
if (file_exists($colors_css)) {
    ?>
    <link rel="stylesheet" href="<?php echo $colors_css; ?>?t=<?php echo time(); ?>">
    <?php
}
if (file_exists($custom_css)) {
    ?>
    <link rel="stylesheet" href="<?php echo $custom_css; ?>?t=<?php echo time(); ?>">
    <?php
}
?>
<style>
/* Video thumbnail styles */
.vfm-video-thumb-wrap{position:relative;background:#1a1a2e}
.vfm-video-thumb{width:100%;height:100%;object-fit:cover;display:block}
.vfm-video-thumb-play{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:rgba(255,255,255,.85);font-size:1.6em;text-shadow:0 2px 8px rgba(0,0,0,.5);pointer-events:none;z-index:2}
.gridview .vfm-video-thumb-play{font-size:2.5em}
.inlinethumbs .vfm-video-thumb-play{font-size:1.4em}
.vfm-video-thumb-canvas{width:100%;height:100%;object-fit:cover;display:block}
</style>
