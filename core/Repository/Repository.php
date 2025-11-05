<?php

namespace Core\Repository;




use Attributes\TargetEntity;
use Core\Attributes\Column;
use Core\Attributes\Table;
use Core\Database\Database;
use ReflectionClass;

abstract class Repository
{
    protected \PDO $pdo;


    protected string $targetEntity;
    protected string $tableName;

    public function __construct()
    {
        $this->pdo =  Database::getPdo();

        $this->targetEntity = $this->resolveTargetEntity();

        $this->tableName = $this->resolveTableName();

    }

    protected function resolveTableName()
    {
        $reflection = new ReflectionClass($this->targetEntity);
        $attributes = $reflection->getAttributes(Table::class);
        $arguments = $attributes[0]->getArguments();
        $tableName = $arguments["name"];
        return $tableName;
    }

    protected function resolveTargetEntity()
    {
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes(TargetEntity::class);
        $arguments = $attributes[0]->getArguments();
        $targetEntity = $arguments["entityName"];
        return $targetEntity;
    }

    protected function resolveColumnsList(): array //retourne un tableau associatif
    {
        $columns = [];
        $reflection = new ReflectionClass($this->targetEntity);
        foreach ($reflection->getProperties() as $property)
        {
            $attributes = $property->getAttributes(Column::class); //itére sur toutes les propriétés et non plus sur la classe en général
            if (!empty($attributes)) { //traite propriété avec attributs le reste oublie
                $args = $attributes[0]->getArguments(); // on traite le premier attribut de chaque propriété
                $columns[$property->getName()] = $args['columnName'] ?? $property->getName(); //récupére le nom de la colonne et si pas de nom dans attribut prend celui de la propriété
            }
        }

        return $columns;
    }



    public function findAll() : array
    {
        $columnName = $this->resolveColumnsList();
        $columns = implode(", ", $columnName);
        $query = $this->pdo->prepare("SELECT $columns FROM $this->tableName");
        $query->execute();
        $items = $query->fetchAll(\PDO::FETCH_CLASS, $this->targetEntity);
        return $items;
    }
    public function find(int $id) : object | bool
    {

        $columnName = $this->resolveColumnsList();
        $column = implode(", ", $columnName);
        $query = $this->pdo->prepare("SELECT $column FROM $this->tableName WHERE id = :id");
        $query->execute([
            "id"=> $id
        ]);
        $query->setFetchMode(\PDO::FETCH_CLASS, $this->targetEntity);
        $item = $query->fetch();
        return $item;
    }

    public function delete(object $item) : void
    {
        $deleteQuery = $this->pdo->prepare("DELETE FROM $this->tableName WHERE id = :id");
        $deleteQuery->execute([
            "id"=> $item->getId()
        ]);

    }

}