<?php
if (! defined('ABSPATH')) {
    die();
}
pf_backwardCompatibility();
/*
 * plugin settings page
 */
global $PhotoFeedAccounts, $PhotoFeedGalleries;
$PhotoFeedAccounts = get_option('photofeed_accounts');
$PhotoFeedGalleries = get_option('photofeed_galleries');

$ig_msgs = [];

// update gallery
if (isset($_POST['pfds-form-update']) && check_admin_referer('pfds-form-nonce')) {
    // filtering data
    $POSTDATA = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // Form Fields
    $PFFFs = pf_validateGSettings($POSTDATA);
    if (! empty($POSTDATA['pff-gid'])) {
        $PhotoFeedGalleries[$POSTDATA['pff-gid']] = $PFFFs;
    } else {
        $PhotoFeedGalleries[] = $PFFFs;
    }
    update_option('photofeed_galleries', $PhotoFeedGalleries, false);

    $ig_msgs[] = __('Gallery updated successfully.', 'photo-feed');
}

// delete gallery
if (isset($_GET['pf-delete-gallery']) && check_admin_referer('pfa-request')) {
    $gid = filter_var($_GET['pf-delete-gallery'], FILTER_SANITIZE_STRING);
    if (isset($PhotoFeedGalleries[$gid])) {
        unset($PhotoFeedGalleries[$gid]);
        update_option('photofeed_galleries', $PhotoFeedGalleries, false);
        $ig_msgs[] = __('Gallery deleted successfully.', 'photo-feed');
    }
}
?>
<div id="photofeed-page" class="wrap">
	<header class="pfa-page-header">
		<h3><?php _e('Photo Feed', 'photo-feed'); ?></h3>
	</header>
	<div class="photofeed-page-content">
        <?php
        if (! empty($ig_msgs)) {
            foreach ($ig_msgs as $ig_msg) {
                echo '<div class="notice updated is-dismissible" ><p>' . $ig_msg . '</p></div>';
            }
        }

        if (isset($GLOBALS['pfa-message']) && ! empty($GLOBALS['pfa-message'])) {
            echo '<div class="notice updated is-dismissible" ><p>' . $GLOBALS['pfa-message'] . '</p></div>';
        }

        if (isset($_GET['tab'])) {
            if ($_GET['tab'] == 'edit') {
                include 'views/gallery.php';
            }
        } else {
            // load accounts section
            include 'views/accounts.php';

            if (! empty($PhotoFeedGalleries)) {
                include 'views/galleries-list.php';
            }
        }
        ?>
    </div>
</div>

