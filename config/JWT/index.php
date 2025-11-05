<?php

use classes\JWT;

require_once 'includes/config.php';
require_once 'classes/JWT.php';

$header = [
    'typ' => 'JWT',
    'alg' => 'HS256'
];

$payload = [
    'user_id' => 123,
    'roles' => ['ROLE_ADMIN', 'ROLE_USER']
];


$jwt = new JWT();
$token = $jwt->generate($header, $payload, SECRET, 900);

echo $token;