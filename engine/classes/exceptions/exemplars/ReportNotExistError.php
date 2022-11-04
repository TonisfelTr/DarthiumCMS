<?php

namespace Exceptions\Exemplars;

class ReportNotExistError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 29);
    }
}