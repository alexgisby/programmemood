<?php

namespace BBC;

/**
 * Me being lazy, making a wrapper around curl so I don't have to deal with Reith.
 */
class cURL
{
    protected $on_reith;

    public function __construct($on_reith = true)
    {
        $this->on_reith = $on_reith;
    }

    public function request($url, $type = 'json')
    {
        $c = curl_init();

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true
        );

        if($this->on_reith) {
            $options[CURLOPT_PROXY] = 'www-cache.reith.bbc.co.uk:80';
        }

        curl_setopt_array($c, $options);
        $response = curl_exec($c);
        curl_close($c);

        if($type == 'json') {
            return json_decode($response);
        } else {
            return $response;
        }
    }
}