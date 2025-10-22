<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\LoginType;
use Attributes\DefaultEntity;
use Core\Attributes\Route;
use Core\Controller\Controller;
use Core\Http\Response;
use Core\Service\JWTService;

#[DefaultEntity(entityName: User::class)]
class AuthController extends Controller
{

    private JWTService $jwtService;

    public function __construct()
    {
        parent::__construct();
        $this->jwtService = new JWTService('JWT_SECRET');
    }

    #[Route(uri: "/login", routeName: "login")]
    public function login(): Response
    {
        $loginForm = new LoginType();

        if ($loginForm->isSubmitted()) {
            $email = $loginForm->getValue("email");
            $password = $loginForm->getValue("password");

            $user = $this->getRepository()->findByEmail($email);

            // Vérifier les credentials
            if ($user && $user->passwordMatches($password)) {

                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                $payload = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ];

                $token = $this->jwtService->generate($header, $payload, $_ENV['JWT_SECRET']);

                //$csrfToken = bin2hex(random_bytes(32));

                setcookie('jwt_token', $token,
                    ['expires' => time() + 3600,
                        'path' => '/',
                       // 'domain' => '127.0.0.1',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'strict',
                    ]
                );


                return $this->redirectToRoute("landing_default");
            }


            return $this->render('auth/login', [
                'error' => 'Email ou mot de passe incorrect'
            ]);
        }

        return $this->render('auth/login', []);
    }

    #[Route(uri: "/logout", routeName: "logout")]
    public function logout(): Response
    {

        return $this->redirectToRoute("auth");
    }

}