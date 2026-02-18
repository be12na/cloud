<?php
/**
 *
 * PHP version >= 5.3
 *
 *
 */
if (!defined('VFM_APP')) {
    return;
}
$privacy_file = 'admin/_content/privacy-info.html';
$privacy = file_exists($privacy_file) ? file_get_contents($privacy_file) : false;
?>
 <footer class="footer small bg-dark-lighter py-4">
    <div class="container">
        <div class="row">
        <div class="col-sm-6">
            <a href="<?php echo $setUp->getConfig('script_url'); ?>">
                <?php echo $setUp->getConfig("appname"); ?>
            </a> &copy; <?php echo date('Y'); ?>
            <?php
            if ($privacy) {
                ?> | 
                <a href="#" data-bs-toggle="modal" data-bs-target="#privacy-info">
                    <?php echo $setUp->getString("privacy"); ?>
                </a>
                <?php
            } ?>
        </div>

        <?php
        // Credits
        if ($setUp->getConfig('hide_credits') !== true) {
            $credits = $setUp->getConfig('credits');
            if ($credits) { ?>
                <div class="col-sm-6 text-sm-end">
                <?php
                if ($setUp->getConfig('credits_link')) { ?>
                    <a target="_blank" href="<?php echo $setUp->getConfig('credits_link'); ?>">
                        <?php echo htmlspecialchars($credits, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php
                } else {
                    echo htmlspecialchars($credits, ENT_QUOTES, 'UTF-8');
                } ?>
                </div>
                <?php
            } else { ?>
                <div class="col-sm-6 text-sm-end">
                    <a title="Built with bikinweeb" target="_blank" href="https://bikinweeb.com">
                         ©BW
                    </a>
                </div>
                <?php
            }
        } ?>
        </div>
    </div>
</footer>
<div class="to-top"><i class="bi bi-chevron-up"></i></div>
<?php
if ($privacy) {
    echo $privacy;
}
