<?php

namespace Exceptions\Exemplars;

class SidePanelNotFoundError extends \Exceptions\TavernException
{
    public function __construct(string $message = "") {
        parent::__construct($message, 35);
    }
}