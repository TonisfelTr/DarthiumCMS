<?php

namespace Exceptions\Exemplars;

class ReportAnswerNotExistError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 30);
    }
}