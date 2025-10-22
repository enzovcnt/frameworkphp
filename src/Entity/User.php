<?php

namespace App\Entity;



use App\Repository\UserRepository;
use Attributes\TargetRepository;
use Core\Attributes\Table;
use Core\Security\UserManagement;

#[Table(name: 'users')]
#[TargetRepository(repoName: UserRepository::class)]
class User extends UserManagement
{

    protected int $id;
    private string $email;
    protected string $password;

    public function getId(): int
    {
        return $this->id;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    public function getPassword(): string
    {
        return $this->password;
    }

    public function getAuthenticator(): string
    {
        return $this->email;
    }



}