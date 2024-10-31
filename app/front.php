<?php
if (! defined('ABSPATH')) {
    die();
}

// Registering assets
add_action('wp_enqueue_scripts', 'photofeed_enqueue_scripts');

function photofeed_enqueue_scripts()
{
    global $PhotoFeedGalleries;
    $have_carousel_layout = false;

    if (! $PhotoFeedGalleries) {
        $PhotoFeedGalleries = get_option('photofeed_galleries');
    }

    if ($PhotoFeedGalleries && is_array($PhotoFeedGalleries)) {
        foreach ($PhotoFeedGalleries as $gallery) {
            if (isset($gallery['pff-layout']) && ($gallery['pff-layout'] == 'carousel')) {
                $have_carousel_layout = true;
            }
        }
    }
    if ($have_carousel_layout) {
        wp_enqueue_style('swiper', PHOTOFEED_URL . '/assets/swiper/swiper.min.css', [], PHOTOFEED_VER);
        wp_enqueue_script('swiper', PHOTOFEED_URL . '/assets/swiper/swiper.min.js', [
            'jquery'
        ], PHOTOFEED_VER, true);
    }

    $suffix = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
    wp_enqueue_style('photo-feed', PHOTOFEED_URL . '/assets/style' . $suffix . '.css', [], PHOTOFEED_VER);
}

// shortcode
add_shortcode('photo-feed', 'cb_shortcode_photofeed');

// shortcode callback
function cb_shortcode_photofeed($atts)
{
    if (! isset($_REQUEST['pfds-form-update-preview'])) {
        pf_backwardCompatibility();
    }
    // update/validate attributes
    $atts = shortcode_atts(array(
        'id' => 1
    ), $atts);

    global $PhotoFeedAccounts, $PhotoFeedGalleries, $PFApi;
    if (! $PhotoFeedAccounts) {
        $PhotoFeedAccounts = get_option('photofeed_accounts');
    }
    if (! $PhotoFeedGalleries) {
        $PhotoFeedGalleries = get_option('photofeed_galleries');
    }

    $results = '';
    if (empty($PhotoFeedAccounts)) {
        if (current_user_can('administrator')) {
            $results .= '<div class="photofeed-no-token">';
            $results .= '<p>' . __('please add an Instagram account in Photo Feed settings panel.', 'photo-feed') . '</p>';
            $results .= '</div>';
        }
        return $results;
    }

    if (! isset($PhotoFeedGalleries[$atts['id']])) {
        if (current_user_can('administrator')) {
            $results .= '<div class="photofeed-invalid-shortcode">';
            $results .= '<p>' . __('not a valid shortcode.', 'photo-feed') . '</p>';
            $results .= '</div>';
        }
        return $results;
    }

    // Display Settings
    $PFDS = $PhotoFeedGalleries[$atts['id']];

    // filter settings
    $PFDS = apply_filters('photo_feed_display_settings', $PFDS);

    if (isset($_REQUEST['pfds-form-update-preview'])) {
        $POSTDATA = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $iGPFFs = pf_validateGSettings($POSTDATA);
        $PFDS = array_merge($PFDS, $iGPFFs);
    }

    $iprofile = ig_getProfile($PFDS['pff-aid']);
    $ifeed = ig_getFeed($PFDS['pff-aid']);

    // filter results
    $ifeed = apply_filters('photo_feed_items', $ifeed);

    $results .= '<div class="photo-feed-block">';

    if (! empty($ifeed)) {
        $tpl = 'grid';
        if (! empty($PFDS['pff-layout'])) {
            $tpl = $PFDS['pff-layout'];
        }
        ob_start();
        include (PHOTOFEED_PATH . "templates/{$tpl}.php");
        $results .= ob_get_clean();
    } else {
        if (current_user_can('administrator')) {
            $results .= '<div class="photo-feed-no-items-msg">';
            $msg = $PFApi->getMessage();
            if (! empty($msg)) {
                $results .= '<p>' . $msg . '</p>';
            }
            $results .= '</div>';
        }
    }

    $results .= '</div> <!-- // Gallery Block -->';
    return $results;
}
