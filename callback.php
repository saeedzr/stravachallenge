<?php 
include 'vendor/autoload.php';

use Strava\API\OAuth;
use Strava\API\Exception;

try {
    $options = array(
        'clientId'     => 12043,
        'clientSecret' => '',
        'redirectUri'  => 'http://saeed.opoint.com/callback.php'
    );
    $oauth = new OAuth($options);

    if (!isset($_GET['code'])) {
        print '<a href="'.$oauth->getAuthorizationUrl().'">connect</a>';
    } else {
        $token = $oauth->getAccessToken('authorization_code', array(
            'code' => $_GET['code']
        ));
        print $token;

        $cmd = "echo 'T:$token' >> /home/storage2/web/pic/tmp/token.txt";
        exec($cmd);
    }
} catch(Exception $e) {
    print $e->getMessage();
}