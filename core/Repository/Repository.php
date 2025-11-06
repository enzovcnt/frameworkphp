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

    public function save(object $item) : void
    {
        $columnArray = $this->resolveColumnsList(); //tableau associatif > propriété => nom colonne
        $data = []; // tableau vide > nom de colonne => valeur > pour les placeholder de la requête :content, :title

        foreach ($columnArray as $property => $column) //récupére le nom de propriété et de de la colonne > les deux sont les mêmes
        {
            if ($property === "id") //passe l'id car auto incrémenté par la base de donnée
            {
                continue;
            }

            $getter = 'get' . ucfirst($property); //cherche le nom du getter pour chaque propriété pour pouvoir récupérer les données
            if (method_exists($item, $getter)) { //vérifie que méthode existe
                $data[$column] = $item->$getter(); //la boucle donne ça title => mon titre > pareil avec le reste
            }
        }

        $columns = implode(', ', array_keys($data)); //sépare avec des , les clefs = nom des colonnes > obligatoire pour la requête SQL
        $placeholders = implode(', ', array_map(fn($col) => ":$col", array_keys($data))); //utile le nom des colonnes pour en faire des placeholder pour la requête
        $query = $this->pdo->prepare("INSERT INTO $this->tableName ($columns) VALUES ($placeholders)");
        $query->execute($data);

    }

    public function update(object $item) : object //renvoie un objet car utilise un objet
    {
        $columnArray = $this->resolveColumnsList();
        $data = [];
        foreach ($columnArray as $property => $column)
        {
            $getter = 'get' . ucfirst($property);
            if (method_exists($item, $getter)) {
                $data[$column] = $item->$getter();
            }
        } //comme save
        $id = $data['id']; //récupére valeur de id = clef primaire
        unset($data['id']); // On enlève l'id du tableau car on ne cherche pas à le changer
        $setChange= implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data))); //pareil que dans save

        $query = $this->pdo->prepare("UPDATE $this->tableName SET $setChange WHERE id = :id");

        // On rajoute l'id dans le tableau pour que toutes les valeurs soient présente
        $data['id'] = $id;

        $query->execute($data);

        return $this->find($id); //retourne l'netité depuis la bdd
    }

}