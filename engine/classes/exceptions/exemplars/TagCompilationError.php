<?php

namespace Exceptions\Exemplars;

use Engine\ErrorManager;
use Exceptions\TavernException;

class TagCompilationError extends TavernException
{
    private string $uncompiledHtml;

    protected $availableCodes = [
        ErrorManager::EC_TAG_COMPILATION_DOES_NOT_ENDED
        ];

    public function __construct(string $htmlContent, int $errorCode = 999, array $replacingContent = []) {
        $this->uncompiledHtml = $htmlContent;
        $this->currentCode = $errorCode;
        //parent::__construct("", $errorCode, $replacingContent);

        ErrorManager::throwCompilationErrorHandlerHtml($this);
        http_response_code(500);
    }

    public function getUncompiledHtml() : string {
        return $this->uncompiledHtml;
    }
}