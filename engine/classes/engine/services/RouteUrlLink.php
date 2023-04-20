<?php

namespace Engine\Services;

use Builder\Controllers\BuildManager;
use Engine\ErrorManager;
use Engine\RouteAgent;
use Exceptions\Exemplars\InvalidRouteIdentificatorError;
use Exceptions\Exemplars\InvalidRouteUrlChainLinkError;
use InvalidArgumentException;

class RouteUrlLink
{
    public const RL_TEXTNAME = 0;
    public const RL_SERVICENAME = 1;
    public const RL_RECEIVEDNAME = 2;

    private $name;
    private $receivedName;
    private $nullable = false;
    private $availableValues = [];
    private $caseSensitive = false;
    private $canContainOnlyNumbers = false;
    private $canContainOnlyLetters = false;
    private $canContainOnlyNumbersAndLetters = false;
    private $containAllAfterIt = false;
    private $isLast = false;

    public function __construct(string $linkContent, bool $isLast = false, bool $containAllAfterIt = false) {
        $this->receivedName = $linkContent;
        if (str_starts_with($linkContent, '{') && str_ends_with($linkContent, '}')) {
            if (str_contains($linkContent, ':')) {
                $pattern = "/\{([a-zA-Z0-9_\-]+):((n){0,1}|(v\[([a-zA-Z0-9_\-\,]+){1,}\]){0,1}|(i){0,1}|(d){0,1}|(l){0,1}|(b){0,1}){1,}\}/";
                preg_match($pattern, $linkContent, $matches);

                if (!isset($matches[1])) {
                    throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_INVALID_URL_CHAIN_LINK_NAME, [RouteAgent::getCurrentRoute()->getUrl()]);
                } else {
                    $this->name = $matches[1];
                }

                $matches = array_slice(array_filter($matches, function ($match) { return $match !== ''; }), 1);

                if (!count($matches)) {
                    throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_INVALID_LINK_IN_URL, [RouteAgent::getCurrentRoute()->getUrl()]);
                }

                foreach ($matches as $index => $match) {
                    if ($match == 'v' && $matches[$index + 1] == '[') {
                        $this->availableValues = explode(',', $matches[$index+1]);
                    }
                    
                    if ($match == 'n') {
                        $this->nullable = true;
                    }
                    if ($match == 's') {
                        $this->caseSensitive = true;
                    }
                    if ($match == 'd') {
                        $this->canContainOnlyNumbers = true;
                    }
                    if ($match == 'l') {
                        $this->canContainOnlyLetters = true;
                    }
                    if ($match == 'b') {
                        $this->canContainOnlyNumbersAndLetters = true;
                    }
                }
            }
            else {
                $pattern = '/\{([a-zA-Z0-9_\-]+)\}/';
                preg_match($pattern, $linkContent, $matches);

                if (!isset($matches[1])) {
                    throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_INVALID_URL_CHAIN_LINK_NAME, [RouteAgent::getCurrentRoute()->getUrl()]);
                }

                $this->name = $matches[1];
            }
        }
        elseif (str_starts_with($linkContent, '[') && str_ends_with($linkContent, ']')) {
            $explodedLinkContent = trim($linkContent, '[]');
            $this->name = $explodedLinkContent;
            if ($this->isLast = $isLast) {
                $this->containAllAfterIt = $containAllAfterIt;
            }
        }

        $this->isLast = $isLast;

        if ($this->containAllAfterIt) {
            if (!preg_match('/[0-9a-zA-Z\-\_\.\@]+/', $this->name)) {
                throw new InvalidRouteIdentificatorError("", ErrorManager::EC_ABSOLUTE_ARGUMENT_HAVE_PROPERTIES);
            }
        }
    }

    public function doesContainAllAfterIt() : bool {
        return $this->containAllAfterIt;
    }

    public function isCorrect($content) : bool {
        $content = $content ?: '';

        if (str_contains($content, '..')) {
            return false;
        }

        if ($this->containAllAfterIt && preg_match('/^[a-zA-Z0-9_\-\.\/\@]+$/', $content)) {
            return true;
        } elseif (str_contains($content, '/')) {
            return false;
        }

        if ($this->caseSensitive && !empty($this->availableValues)  && (!$this->nullable || $content != '')) {
            foreach ($this->availableValues as $value) {
                if (strcmp($value, $content) !== 0) {
                    return false;
                }
            }
        }

        if (!empty($this->availableValues)) {
            if (!in_array($content, $this->availableValues) && (!$this->nullable || $content != '')) {
                return false;
            }
        }

        if (!$this->nullable) {
            if (($content == null || $content == "" || strlen(trim($content)) == 0)) {
                return false;
            }
        }

        if ($this->nullable && !$this->isLast) {
            throw new InvalidArgumentException("A non-last argument cannot be empty");
        }

        if ($this->canContainOnlyNumbersAndLetters) {
            if (!preg_match("/[a-zA-Z0-9]+/", $content) && (!$this->nullable || $content != '')) {
                return false;
            }
        }
        elseif ($this->canContainOnlyNumbers) {
            if (!preg_match("/[0-9]+/", $content) && (!$this->nullable || $content != '')) {
                return false;
            }
        } elseif ($this->canContainOnlyLetters) {
            if (!preg_match("/[a-zA-Z]+/", $content) && (!$this->nullable || $content != '')) {
                return false;
            }
        } elseif ($this->canContainOnlyLetters && $this->canContainOnlyNumbers) {
            if (!(preg_match("/[a-zA-Z]+/", $content) ^ preg_match("/[0-9]+/", $content)) && (!$this->nullable || $content != '')) {
                return false;
            }
        }

        return true;
    }

    public function getName(int $flag = self::RL_SERVICENAME) : string {
        switch ($flag) {
            case self::RL_TEXTNAME:
                return $this->name;
            case self::RL_SERVICENAME:
                return $this->containAllAfterIt ? '[' . $this->name . ']' : '{' . $this->name . '}';
            case self::RL_RECEIVEDNAME:
                return $this->receivedName;
        }

        return $this->receivedName;

    }

    public function canBeNull() : bool {
        return $this->nullable;
    }

    public function setAvailableValues(array $values) {
        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($value as $varValue) {
                    $this->availableValues[] = $varValue;
                }
            } elseif (is_string($value) || is_integer($value)) {
                $this->availableValues[] = $value;
            }
        }

        return $this;
    }

    public function isLast() : bool {
        return $this->isLast();
    }
}