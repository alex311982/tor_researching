<?php

define('NUMBER_TIMES_RELOAD_IP', 3);
define('PARSE_URL', 'https://www.amazon.com/AmazonBasics-360-Piece-Clear-Plastic-Cutlery/dp/B010RLC7P2/ref=sr_1_1?s=kitchen&srs=10112675011&ie=UTF8&qid=1509179091&sr=1-1');
define('TOR_PASSWORD', 'my_password');
define('SEARCH_BLOCKED_REGEXP', 'robot');

// Autoloading using composer
require 'vendor/autoload.php';

function get($url,$proxy) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208');
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_PROXY, "$proxy");
    $ss = curl_exec($ch);
    curl_close($ch);
    return $ss;
}

function reloadIP()
{
    // Connect to the TOR server using password authentication
    $tc = new TorControl\TorControl(
        array(
            'hostname' => 'localhost',
            'port'     => 9051,
            'password' => TOR_PASSWORD,
            'authmethod' => 1
        )
    );

    $tc->connect();

    $tc->authenticate();

// Renew identity
    $res = $tc->executeCommand('SIGNAL NEWNYM');

// Quit
    $tc->quit();

    // Echo the server reply code and message
    return $res[0]['code'].': '.$res[0]['message'];
}

$prox = 'localhost:9050';
$j = 0;
for ($i=1; $i<=5;$i++) {
    $a = get(PARSE_URL,$prox);
    preg_match('/' . SEARCH_BLOCKED_REGEXP .'/', $a, $matches) == 1 ? $j++ : null;
}

if ($j >= NUMBER_TIMES_RELOAD_IP) {
    $res = reloadIP();
    echo "It was $j capcha forms for robots and result of reload IP is $res\n";
} else {
    echo "It was $j capcha forms for robots and IP was not reloaded\n";
}
