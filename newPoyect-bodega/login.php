<?php
session_start();
$config = require 'config.php';

$authUrl = "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/authorize?" .
    http_build_query([
        'client_id' => $config['clientId'],
        'response_type' => 'code',
        'redirect_uri' => $config['redirectUri'],
        'response_mode' => 'query',
        'scope' => $config['scopes'],
    ]);

header('Location: ' . $authUrl);
exit();
?>
