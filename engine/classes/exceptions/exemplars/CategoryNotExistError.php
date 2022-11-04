<?php

namespace Exceptions\Exemplars;

class CategoryNotExistError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 32);
    }
}