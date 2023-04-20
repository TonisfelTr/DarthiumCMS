<?php

namespace Exceptions;

use Builder\Controllers\BuildManager;
use Engine\ErrorManager;
use Error;
use Exception;
use Exceptions\Exemplars\UnavailabledErrorCodeError;
use Throwable;

class TavernException extends Error //implements Throwable
{
    protected $availableCodes = [];
    protected $message;
    protected int $currentCode;

    public function __construct(string $message = "", int $errorCode = 999, array $replacingContent = []) {
        header_remove("Content-Type");
        header("Content-Type: text/html;charset=UTF-8");
        if (!empty($this->availableCodes) && !in_array($errorCode, $this->availableCodes)) {
            throw new UnavailabledErrorCodeError();
        }

        ErrorManager::throwIfErrorCodeInvalid($errorCode);
        if ($message == "") {
            if (count($this->availableCodes) == 1) {
                $errorCode = reset($this->availableCodes);
            }

            $message = ErrorManager::getErrorDescription($errorCode);
        }
        parent::__construct($message ?: ErrorManager::getErrorDescription($errorCode), 500);

        $this->code = 500;
        $this->message = $message;
        $this->currentCode = $errorCode;

        if ($firstBracket = strpos($this->message, '{')) {
            if ($secondBracket = strpos($this->message, '}')) {
                if ($firstBracket < $secondBracket) {
                    $replacingPlaces = [];
                    if (!empty($replacingContent)) {
                        preg_match_all("/(\{[a-zA-Z0-9_\-]+\})/", $this->message, $replacingPlaces);
                        foreach ($replacingPlaces[1] as $index => $replacingPlace) {
                            $this->message = str_replace($replacingPlace, $replacingContent[$index], $this->message);
                        }
                    } else {
                        preg_match_all("/(\{[a-zA-Z0-9_\-]+\})/", $this->message, $replacingPlaces);
                        foreach ($replacingPlaces[1] as $index => $replacingPlace) {
                            $this->message = str_replace($replacingPlace, '', $this->message);
                        }
                    }
                }
            }
        }

        BuildManager::turnOffOutputBuffering();
        ErrorManager::throwExceptionHandlerHtml($this);
    }

    public function getErrorCode() : int {
        return $this->currentCode;
    }
}