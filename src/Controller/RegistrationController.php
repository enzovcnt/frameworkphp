<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Attributes\DefaultEntity;
use Core\Attributes\Route;
use Core\Controller\Controller;
use Core\Http\Response;

#[DefaultEntity(entityName: User::class)]
class RegistrationController extends Controller
{


    #[Route(uri: "/register", routeName: "register")]
    public function create():Response
    {

        $registrationForm = new RegistrationType();
        if($registrationForm->isSubmitted()){

            $user = new User();
            $user->setEmail($registrationForm->getValue("email"));
            $user->setPassword($registrationForm->getValue("password"));
            $user = $this->getRepository()->save($user);
            return $this->redirectToRoute("");


        }




        return $this->render('register/registration', []);
    }
}