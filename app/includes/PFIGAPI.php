<?php
if (! defined('ABSPATH')) {
    die();
}

// Photo Feed Instagram API handling
class PFIGAPI
{

    protected $instagramAPI;

    public $message;

    public function __construct()
    {
        $this->instagramAPI = 'https://graph.instagram.com';
        $this->message = '';
    }

    /**
     * get user profile info
     * @return false or array
     */
    public function getUserProfileInfo($access_token)
    {
        $url = $this->instagramAPI . '/me?fields=id,username,account_type,media_count&access_token=' . $access_token;

        $response = $this->spider($url);
        if (empty($response)) {
            return false;
        }
        $response = json_decode($response, true);

        if (isset($response['error']['message'])) {
            $this->message = $response['error']['message'];
            return false;
        }

        return (isset($response['id']) && isset($response['username'])) ? $response : false;
    }

    // API call to get user feed
    public function getUserMedia($access_token)
    {
        $url = $this->instagramAPI . '/me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username&access_token=' . $access_token;

        $response = $this->spider($url);
        if (empty($response)) {
            return false;
        }
        $response = json_decode($response, true);

        if (isset($response['error']['message'])) {
            $this->message = $response['error']['message'];
            return false;
        }

        if (! isset($response['data'])) {
            return false;
        }

        return $this->setupMedia($response['data']);
    }

    protected function setupMedia($data)
    {
        $instaItems = [];
        if (is_array($data) && ! empty($data)) {
            foreach ($data as $item) {
                $instaItems[] = [
                    'id' => $item['id'],
                    'caption' => isset($item['caption']) ? $item['caption'] : '',
                    'media_type' => $item['media_type'],
                    'media_url' => $item['media_url'],
                    'thumbnail_url' => isset($item['thumbnail_url']) ? $item['thumbnail_url'] : '',
                    'permalink' => $item['permalink'],
                    'timestamp' => $item['timestamp'],
                    'username' => $item['username']
                ];
            }
        }
        return $instaItems;
    }

    // API call to get user feed
    public function refreshToken($access_token)
    {
        $url = $this->instagramAPI . "/refresh_access_token?grant_type=ig_refresh_token&access_token={$access_token}";

        $response = $this->spider($url);
        if (empty($response)) {
            return false;
        }
        $response = json_decode($response, true);

        if (isset($response['error']['message'])) {
            $this->message = $response['error']['message'];
            return false;
        }

        if (isset($response['access_token'])) {
            return $response;
        }
        return false;
    }

    /**
     * remote request
     *
     * @param string $url
     * @return string|boolean
     */
    public function spider($url)
    {
        if (empty($url) || (! filter_var($url, FILTER_VALIDATE_URL))) {
            $this->message = 'invalid URL';
            return false;
        }
        $responseBody = '';

        if (function_exists('wp_remote_request')) {
            $response = wp_remote_request($url);
            if (is_wp_error($response)) {
                $this->message = 'WP Error: ' . implode(', ', $response->get_error_messages());
            } else {
                if (200 !== wp_remote_retrieve_response_code($response)) {
                    $this->message = 'Error: response code: ' . wp_remote_retrieve_response_code($response);
                }

                if (isset($response['body']) && ! empty($response['body'])) {
                    $responseBody = $response['body'];
                }
            }
        } else {
            $this->message = 'Error: running outside WP.';
        }

        return $responseBody;
    }

    // return message
    public function getMessage()
    {
        return $this->message;
    }
}
