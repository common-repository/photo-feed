<?php
if (! defined('ABSPATH')) {
    die();
}

global $PFApi;

include_once (PHOTOFEED_PATH . 'app/includes/functions.php');
include_once (PHOTOFEED_PATH . 'app/includes/PFIGAPI.php');
include_once (PHOTOFEED_PATH . 'app/includes/PHOTOFEED_Widget.php');

$PFApi = new PFIGAPI();

// handle preview gallery request
add_action('wp_ajax_pfa_preview_gallery', function () {
    if (! check_admin_referer('pfds-form-nonce')) {
        wp_send_json_error(__('Invalid Request.', 'photo-feed'));
    }
    $gid = isset($_POST['pf-gid']) ? $_POST['pf-gid'] : 1;
    wp_send_json_success(cb_shortcode_photofeed($gid));
});

// handle admin requests
add_action('admin_init', function () {
    if (! current_user_can('administrator')) {
        return;
    }

    // handle profile actions
    if (isset($_POST['pfa-profile-id']) && isset($_POST['pfa-profile-action']) && check_admin_referer('pfa-request')) {
        $action = filter_input(INPUT_POST, 'pfa-profile-action', FILTER_SANITIZE_STRING);
        $profile_id = filter_input(INPUT_POST, 'pfa-profile-id', FILTER_SANITIZE_STRING);
        switch ($action) {
            case 'remove':
                global $PhotoFeedAccounts;
                if (! $PhotoFeedAccounts) {
                    $PhotoFeedAccounts = get_option('photofeed_accounts');
                }
                if (isset($PhotoFeedAccounts[$profile_id])) {
                    unset($PhotoFeedAccounts[$profile_id]);
                    update_option('photofeed_accounts', $PhotoFeedAccounts, false);
                }
                $GLOBALS['pfa-message'] = __('Removed successfully.', 'photo-feed');
                break;
            case 'cache':
                pf_clearTransients([
                    'photofeed_profiles',
                    'photofeed_user_' . $profile_id
                ]);
                $GLOBALS['pfa-message'] = __('Refreshed successfully.', 'photo-feed');
                break;
        }
    }
});

// save token
add_action('admin_init', function () {
    if (! current_user_can('administrator')) {
        return;
    }
    $pfitrs = 'photofeed' . '-pfatret';
    $igpanel = admin_url('options-general.php?page=photo-feed');
    if (isset($_GET[$pfitrs])) {
        if (! empty($_GET['ig_access_token'])) {
            $token = filter_var($_GET['ig_access_token'], FILTER_SANITIZE_STRING);
            if ($token) {
                global $PhotoFeedAccounts, $PhotoFeedGalleries, $PFApi;
                if (! $PhotoFeedAccounts || ! is_array($PhotoFeedAccounts)) {
                    $PhotoFeedAccounts = get_option('photofeed_accounts');
                }

                // check again for empty string
                if (empty($PhotoFeedAccounts)) {
                    $PhotoFeedAccounts = [];
                }

                // get account ID
                $profileInfo = $PFApi->getUserProfileInfo($token);
                if ($profileInfo && isset($profileInfo['id'])) {

                    $PhotoFeedAccounts[$profileInfo['id']]['access_token'] = $token;

                    $atexpts = 0;
                    if (isset($_GET['ig_atexpin'])) {
                        $atexpin = filter_var($_GET['ig_atexpin'], FILTER_VALIDATE_INT);
                        if ($atexpin && ($atexpin > 86400)) {
                            $atexpts = strtotime("+ {$atexpin} seconds");
                        }
                    }
                    $PhotoFeedAccounts[$profileInfo['id']]['atexpts'] = $atexpts;

                    update_option('photofeed_accounts', $PhotoFeedAccounts, false);
                    pf_clearTransients();

                    // add first Gallery
                    if (! $PhotoFeedGalleries || ! is_array($PhotoFeedGalleries)) {
                        $PhotoFeedGalleries = get_option('photofeed_galleries');
                    }
                    if (empty($PhotoFeedGalleries)) {
                        $defaultGSettings = ig_defaultGSettings();
                        $defaultGSettings['pff-aid'] = $profileInfo['id'];
                        $PhotoFeedGalleries[1] = $defaultGSettings;
                        update_option('photofeed_galleries', $PhotoFeedGalleries, false);
                    }
                }

                // schedule refresh token event
                if (! wp_next_scheduled('pf_cron_hook')) {
                    wp_schedule_event(time(), 'weekly', 'pf_cron_hook');
                }

                if (wp_redirect($igpanel)) {
                    exit();
                }
            }
        }

        // redirect to admin dashboard
        if (! empty($_GET['ig_message'])) {
            exit(filter_var($_GET['ig_message'], FILTER_SANITIZE_STRING));
        } else {
            if (wp_redirect($igpanel)) {
                exit();
            }
        }
    }
});					