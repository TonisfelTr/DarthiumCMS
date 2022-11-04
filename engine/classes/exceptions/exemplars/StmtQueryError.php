<?php

namespace Exceptions\Exemplars;

class StmtQueryError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 9);
    }
}