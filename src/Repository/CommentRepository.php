<?php

namespace App\Repository;




use App\Entity\Comment;
use App\Entity\Post;
use Attributes\TargetEntity;
use Core\Repository\Repository;

#[TargetEntity(entityName: Comment::class)]
class CommentRepository extends Repository
{


    public function getCommentsByPost(Post $post) : array
    {
       $query = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE post_id = :post_id");

       $query->execute([
            'post_id' => $post->getId()
        ]);

        return $query->fetchAll(\PDO::FETCH_CLASS, $this->targetEntity);



    }




    public function save(Comment $comment) : Comment
    {

        $this->pdo->prepare("INSERT INTO $this->tableName (content, post_id) VALUES (:content, :post_id)")->execute([
            "content"=>$comment->getContent(),
            "post_id"=>$comment->getPostId()
        ]);

        return $this->find($this->pdo->lastInsertId());
    }
}