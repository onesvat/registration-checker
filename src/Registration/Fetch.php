<?php
/**
 * Created by PhpStorm.
 * User: onur
 * Date: 06/06/2017
 * Time: 21:52
 */

namespace Registration;


class Fetch
{

    private $cookie_jar;
    private $cookie_file;

    /**
     * Fetch constructor.
     * @param $cookie_jar
     * @param $cookie_file
     */
    public function __construct($cookie_jar, $cookie_file)
    {
        $this->cookie_jar = $cookie_jar;
        $this->cookie_file = $cookie_file;
    }

    public function get($url)
    {
        return $this->request("get", $url);
    }

    public function post($url, $params)
    {
        return $this->request("post", $url, http_build_query($params));
    }

    private function request($type, $url, $data = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($type == "post") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);

        $headers = [
            'Pragma: no-cache',
            'Origin: http://registration.boun.edu.tr',
            'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.6,en;q=0.4',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: no-cache',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Referer: http://registration.boun.edu.tr/studententry.htm',
            'User-Agent: ' . $this->getRandomUserAgent(),
            'Connection: keep-alive'
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    private function getRandomUserAgent()
    {
        $str = file_get_contents(__DIR__ . "/../../data/user_agents.json");
        $user_agents = json_decode($str, true);

        return $user_agents[mt_rand(0, count($user_agents) - 1)]['user_agent'];
    }
}