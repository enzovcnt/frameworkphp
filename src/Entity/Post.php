<?php

namespace App\Entity;


use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Attributes\TargetRepository;
use Core\Attributes\Column;
use Core\Attributes\Table;

#[Table(name: 'posts')]
#[TargetRepository(repoName:PostRepository::class)]
class Post
{


    #[Column(columnName: 'id', columnNullable: false)]
    private int $id;
    #[Column(columnName: 'title', columnLength: 255, columnNullable: false)]
    private string $title;
    #[Column(columnName: 'content',columnType: 'text', columnNullable: false)]
    private string $content;



    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getComments()
    {
        $commentRepository = new CommentRepository();
        return $commentRepository->getCommentsByPost($this);
    }

}