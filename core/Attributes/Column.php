<?php

namespace Core\Attributes;


use Attribute;

#[Attribute]
class Column
{



    public function __construct(
        public string $columnName,
        public ?string $columnType = null,
        public ?int $columnLength = null,
        public bool $columnNullable = false,
    )
    {
    }

}