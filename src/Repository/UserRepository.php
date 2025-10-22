<?php

namespace App\Repository;

use App\Entity\User;
use Attributes\TargetEntity;
use Core\Repository\Repository;


#[TargetEntity(entityName: User::class)]
class UserRepository extends Repository
{

    public function findByEmail(string $email): ?User
    {
        $query = $this->pdo->prepare("SELECT email FROM $this->tableName WHERE email = :email");
        $query->execute([
            "email"=> $email
        ]);
        $query->setFetchMode(\PDO::FETCH_CLASS, $this->targetEntity);
        $email = $query->fetch();
        return $email;
    }

    public function save(User $user): int
    {
        $this->pdo->prepare("INSERT INTO $this->tableName (email, password) VALUES (:email, :password)")
            ->execute([
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
            ]);

        return $this->pdo->lastInsertId();
    }



}