<?php

use classes\JWT;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//interdit toutes méthodes non post
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

//vérifier si reçoit token
if(isset($_SERVER['Authorization']))
{
    $token = trim($_SERVER['Authorization']);
}
elseif(isset($_SERVER['HTTP_AUTHORIZATION']))
{
    $token = trim($_SERVER['HTTP_AUTHORIZATION']);
}
elseif(function_exists('apache_request_headers'))
{
    $requestHeaders = apache_request_headers();
    if(isset($requestHeaders['Authorization']))
    {
        $token = trim($requestHeaders['Authorization']);
    }
}

//vérifie qu'on a un token

if(!isset($token) || !preg_match('/Bearer\s(\S+)/', $token, $matches))
{
    http_response_code(400);
    echo json_encode(['message' => 'introuvable token']);
    exit;
}

//extrait token

$token = str_replace('Bearer ', '', $token); //enlève le bearer dans le token pour juste le token


$jwt = new JWT();

//validité

if(!$jwt->isValid($token))
{
    http_response_code(400);
    echo json_encode(['message' => 'invalid token']);
    exit;
}
//signature
if(!$jwt->check($token, $_ENV['JWT_SECRET']))
{
    http_response_code(403);
    echo json_encode(['message' => 'invalide token']);
    exit;
}

//expiration
if($jwt->isExpired($token))
{
    http_response_code(403);
    echo json_encode(['message' => 'expired token']);
    exit;
}
echo json_encode($jwt->getPayload($token));