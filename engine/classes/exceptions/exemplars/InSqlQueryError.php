<?php

namespace Exceptions\Exemplars;

class InSqlQueryError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 33);
    }
}