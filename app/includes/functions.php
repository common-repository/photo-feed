<?php
if (! defined('ABSPATH')) {
    die();
}

// print the shortcode
if (! function_exists('PhotoFeed')) {
    function PhotoFeed($gallery_id = 1)
    {
        echo do_shortcode('[photo-feed id="' . $gallery_id . '"]');
    }
}

// update old DB (1.x) settings @deprecated
function pf_backwardCompatibility()
{
    global $PFApi;
    $PhotoFeedSettings = get_option('photofeed_settings');
    if ($PhotoFeedSettings && ! empty($PhotoFeedSettings['access_token'])) {
        $token = $PhotoFeedSettings['access_token'];
        global $PhotoFeedAccounts, $PhotoFeedGalleries;
        $PhotoFeedAccounts = get_option('photofeed_accounts');
        $PhotoFeedGalleries = get_option('photofeed_galleries');

        $profileInfo = $PFApi->getUserProfileInfo($token);
        if ($profileInfo && isset($profileInfo['id'])) {
            // update account
            $PhotoFeedAccounts[$profileInfo['id']]['access_token'] = $token;

            if (isset($PhotoFeedSettings['atexpts'])) {
                $PhotoFeedAccounts[$profileInfo['id']]['atexpts'] = $PhotoFeedSettings['atexpts'];
            }
            update_option('photofeed_accounts', $PhotoFeedAccounts, false);

            // update gallery
            if (! empty($PhotoFeedSettings['DisplaySettings'])) {
                $galSettings = $PhotoFeedSettings['DisplaySettings'];
            } else {
                $galSettings = ig_defaultGSettings();
            }
            $galSettings['pff-aid'] = $profileInfo['id'];
            $galSettings['pff-gname'] = 'My First Gallery';
            if (empty($PhotoFeedGalleries)) {
                $PhotoFeedGalleries[1] = $galSettings;
            } else {
                $PhotoFeedGalleries[] = $galSettings;
            }
            update_option('photofeed_galleries', $PhotoFeedGalleries, false);

            delete_option('photofeed_settings');
        }
    }
}

// clear transients
function pf_clearTransients($tks = [])
{
    if ($tks) {
        foreach ($tks as $tk) {
            delete_transient($tk);
        }
        return;
    }

    delete_transient('photofeed_profiles');

    global $PhotoFeedAccounts;
    if ($PhotoFeedAccounts) {
        foreach ($PhotoFeedAccounts as $aid => $pf_account) {
            delete_transient('photofeed_user_' . $aid);
        }
    }
}

// cron action
function pf_refresh_tokens()
{
    ig_refreshToken();
}

// generate code generation url
function ig_getOAuthURL()
{
    $oauthURL = 'https://www.instagram.com/oauth/authorize/';
    $return_uri = urlencode('https://thealpinepress.com/auth/');
    $state_uri = urlencode(ig_getIGStateURI());
    $app_id = "621225125153883";
    $acs = [
        'clientID' => 'NDcxMTc0MjEwMjA1OTYx',
        'redURI' => 'aHR0cHM6Ly9waG90b2ZlZWQuZ2EvaWF1dGgv'
    ];
    $AppCons = array_map('base64_decode', $acs);
    $red_uri = 'https://thealpinepress.com/auth/';
    $oauthURL .= "?app_id={$app_id}&response_type=code&scope=user_profile,user_media&state={$state_uri}&redirect_uri={$red_uri}";
    return $oauthURL;
}

// return array: Instagram account profiles
function ig_getProfiles()
{
    global $PhotoFeedAccounts, $PFApi;
    $profiles = [];

    if (empty($PhotoFeedAccounts)) {
        return $profiles;
    }
    $tk = 'photofeed_profiles';
   
        foreach ($PhotoFeedAccounts as $aid => $pf_account) {
            $profileInfo = $PFApi->getUserProfileInfo($pf_account['access_token']);
            if (! empty($profileInfo)) {
                $profiles[$aid] = $profileInfo;
            }
        }
      
    return $profiles;
}

// return profile info
function ig_getProfile($account_id)
{
    $profileInfo = [];
    $profiles = ig_getProfiles();
    if (isset($profiles[$account_id])) {
        $profileInfo = $profiles[$account_id];
    }
    return $profileInfo;
}

// get user feed
function ig_getFeed($account_id)
{
    global $PhotoFeedAccounts, $PFApi;
    $instaItems = [];

    $tk = 'photofeed_feed_' . $account_id; // transient key
                                           // Get any existing copy of our transient data
    if (false === ($instaItems = get_transient($tk))) {
        // ig_refreshToken();

        // also scheduled here for backward compatibility
        // will be removed in future from here
        if (! wp_next_scheduled('pf_cron_hook')) {
            wp_schedule_event(time(), 'weekly', 'pf_cron_hook');
        }

        // add custom feed, your feed should be an array of items
        $instaItems = apply_filters('your_ifeed', []);

        if (empty($instaItems)) {
            if (isset($PhotoFeedAccounts[$account_id]['access_token'])) {
                $token = $PhotoFeedAccounts[$account_id]['access_token'];
                $instaItems = $PFApi->getUserMedia($token);
            }
        }
        if ($instaItems && ! empty($instaItems)) {
            set_transient($tk, $instaItems, 6 * HOUR_IN_SECONDS);
        }
    }

    return $instaItems;
}

// refresh token before expiration
function ig_refreshToken()
{
    global $PhotoFeedAccounts, $PFApi;

    // recheck if settings inialized for CRON event
    if (! $PhotoFeedAccounts || ! is_array($PhotoFeedAccounts)) {
        $PhotoFeedAccounts = get_option('photofeed_accounts');
    }

    if (empty($PhotoFeedAccounts)) {
        return false;
    }

    $refreshed_anyone = false;
    foreach ($PhotoFeedAccounts as $aid => $account) {
        $atexpts = $account['atexpts'];
        $atexpts = (int) $atexpts;
        if ($atexpts) {
            $diff = $atexpts - strtotime("now");
            if ($diff > 86400) {
                $days = round($diff / 86400);

                if ($days < 15) {
                    $response = $PFApi->refreshToken($account['access_token']);
                    if ($response && isset($response['access_token'])) {
                        $token = $response['access_token'];
                        $atexpts = 0;
                        if (isset($response['expires_in'])) {
                            $atexpin = filter_var($response['expires_in'], FILTER_VALIDATE_INT);
                            if ($atexpin && ($atexpin > 86400)) {
                                $atexpts = strtotime("+ {$atexpin} seconds");
                            }
                        }

                        $account['access_token'] = $token;
                        $account['atexpts'] = $atexpts;
                        $PhotoFeedAccounts[$aid] = $account;
                        $refreshed_anyone = true;
                    }
                }
            }
        }
    }

    // update option if token refreshed
    if ($refreshed_anyone) {
        update_option('photofeed_accounts', $PhotoFeedAccounts, false);
    }

    // refresh feed
    foreach ($PhotoFeedAccounts as $aid => $account) {
        $tk = 'photofeed_feed_' . $aid; // transient key
                                        // Get any existing copy of our transient data
        if (false === ($instaItems = get_transient($tk))) {
            $instaItems = $PFApi->getUserMedia($account['access_token']);
            if (! empty($instaItems)) {
                set_transient($tk, $instaItems, 6 * HOUR_IN_SECONDS);
            }
        }
    }
    return true;
}

// return url from Instagram
function ig_getIGReturnURI()
{
    return admin_url('options-general.php?page=photo-feed');
}

// maintain state of the request
function ig_getIGStateURI()
{
    return admin_url('options-general.php?photofeed-pfatret=1');
}

// validate Gallery Settings
function pf_validateGSettings($POSTDATA)
{
    $PFFFs = [];
    $PFFFs['pff-gname'] = trim(esc_html($POSTDATA['pff-gname']));
    $PFFFs['pff-aid'] = $POSTDATA['pff-aid'];
    $PFFFs['pff-layout'] = $POSTDATA['pff-layout'];
    $PFFFs['pff-cols'] = empty($POSTDATA['pff-cols']) ? 3 : $POSTDATA['pff-cols'];
    $PFFFs['pff-car-ipv'] = empty($POSTDATA['pff-car-ipv']) ? 4 : $POSTDATA['pff-car-ipv'];
    $PFFFs['pff-car-autoplay'] = empty($POSTDATA['pff-car-autoplay']) ? 0 : $POSTDATA['pff-car-autoplay'];
    $PFFFs['pff-car-nav'] = (isset($POSTDATA['pff-car-nav'])) ? $POSTDATA['pff-car-nav'] : 0;
    $PFFFs['pff-car-nav-color'] = sanitize_text_field($POSTDATA['pff-car-nav-color']);
    $PFFFs['pff-limit'] = empty($POSTDATA['pff-limit']) ? 12 : $POSTDATA['pff-limit'];
    $PFFFs['pff-exclude-video'] = (isset($POSTDATA['pff-exclude-video'])) ? $POSTDATA['pff-exclude-video'] : 0;
    $PFFFs['pff-spacing'] = empty($POSTDATA['pff-spacing']) ? 0 : $POSTDATA['pff-spacing'];
    $PFFFs['pff-hover'] = (isset($POSTDATA['pff-hover'])) ? $POSTDATA['pff-hover'] : 0;
    $PFFFs['pff-hover-color'] = sanitize_text_field($POSTDATA['pff-hover-color']);
    $PFFFs['pff-type-icon'] = (isset($POSTDATA['pff-type-icon'])) ? $POSTDATA['pff-type-icon'] : 0;
    $PFFFs['pff-instalink'] = (isset($POSTDATA['pff-instalink'])) ? $POSTDATA['pff-instalink'] : 0;
    $PFFFs['pff-instalink-text'] = trim(esc_html($POSTDATA['pff-instalink-text']));

    if (empty($PFFFs['pff-gname'])) {
        $PFFFs['pff-gname'] = 'My New Gallery';
    }
    if (empty($PFFFs['pff-instalink-text'])) {
        $PFFFs['pff-instalink-text'] = 'Follow on Instagram';
    }

    return $PFFFs;
}

// gallery default settings
function ig_defaultGSettings()
{
    $PFFFs = [
        'pff-gname' => 'My New Gallery',
        'pff-layout' => 'grid',
        'pff-cols' => 3,
        'pff-car-ipv' => 4,
        'pff-car-autoplay' => 3,
        'pff-car-nav' => 1,
        'pff-car-nav-color' => '#007aff',
        'pff-limit' => 12,
        'pff-exclude-video' => 0,
        'pff-spacing' => 10,
        'pff-hover' => 1,
        'pff-hover-color' => '#007aff',
        'pff-type-icon' => 1,
        'pff-instalink' => 0,
        'pff-instalink-text' => 'Follow on Instagram'
    ];

    return $PFFFs;
}
