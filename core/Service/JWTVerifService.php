<?php

namespace Core\Service;

use Core\Service\JWTService;

class JWTVerifService
{


    private JWTService $jwt;
    private string $secret;

    public function __construct(string $secret)
    {
        $this->jwt = new JWTService($secret);
        $this->secret = $secret;
    }

    public function checkToken(): array
    {
        // Récupérer le token depuis l’header Authorization
        $token = null;

        //récupère dans les cookies vu que stocké dedans
        if (isset($_COOKIE['jwt_token'])) {
            $token = $_COOKIE['jwt_token'];
        }

        if (isset($_SERVER['Authorization'])) {
            $token = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $token = trim($requestHeaders['Authorization']);
            }

            if ($token && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
                $token = $matches[1];
            }
        }

        // Vérification existence et format
        if (!$token) {
            throw new \Exception('Token introuvable', 401);
        }


        $token = str_replace('Bearer ', '', $token);

        // Vérifications
        if (!$this->jwt->isValid($token)) {
            throw new \Exception('Token invalide', 400);
        }

        if (!$this->jwt->check($token, $this->secret)) {
            throw new \Exception('Token non autorisé', 403);
        }

        if ($this->jwt->isExpired($token)) {
            throw new \Exception('Token expiré', 403);
        }

        // Tout est OK → retourner le payload
        return $this->jwt->getPayload($token);
    }
}