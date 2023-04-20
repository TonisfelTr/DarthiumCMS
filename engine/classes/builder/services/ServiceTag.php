<?php

namespace Builder\Services;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Builder\Services\Tag;
use Engine\ErrorManager;
use Exceptions\Exemplars\TagError;

class ServiceTag extends Tag
{
    private const    CUSTOM_BODY = '\s*(.+?)\s*';
    private const    CUSTOM_ARGUMENT = '(:\s*(.+?)\s*){0,}';
    private const    ARGUMENT_DEFINED = '\s*([a-zA-Z0-9_\-\!\@\$\%\&\*\(\)\[\]\>\?\.\\\\\|\/\+\=]+){1}';
    private const    ARGUMENT_AVAILABLE = '\s*([a-zA-Z0-9_\-\!\@\$\%\&\*\(\)\[\]\>\?\.\\\\\|\/\+\=]+){0,1}';
    private const    ARGUMENT_POSSIBLE = '\s*([a-zA-Z0-9_\-\!\@\$\%\&\*\(\)\[\]\>\?\.\\\\\|\/\+\=]+){0,}';
    private const    ARGUMENT_VALIDATION = '/\s*[a-zA-Z0-9_\-\!\@\$\%\&\*\(\)\[\]\>\?\.\\\\\|\/\+\=]+\s*/ms';
    private const    BODY_CONTENT = '([a-zA-Z_\-]+)';
    private const    NAME_CONTENT = '([a-zA-Z_\-\!\@\$\%\&\*\>\?\.]+)';

    protected        $processingFunction;
    protected bool   $onlyForOneCompilation = false;
    protected string $tagName;
    private int      $tagArguments;
    private int      $tagRequireArguments;
    private string   $tagPattern;
    private bool     $unknownArgumentsCount = false;

    private function validateArguments(array $argumentsList) : void {
        $tmpMatches = $argumentsList;
        $validated  = array_filter($tmpMatches, function ($match) {
            $arguments = explode(',', $match);
            foreach ($arguments as $argument) {
                if (preg_match(self::ARGUMENT_VALIDATION, $argument) === false) {
                    throw new TagError("", ErrorManager::EC_INVALID_TAG_ARGUMENTS, [$this->tagName, $argument]);
                }
            }

            return true;
        });
    }

    private function validateContent(string $content) : void {
        $pattern = '/' . self::BODY_CONTENT . '/ms';
        if (!preg_match($pattern, $content)) {
            throw new TagError("", ErrorManager::EC_INVALID_TAG_CONTENT, [$this->tagName, $content]);
        }
    }

    private function replaceByPattern(array $matches) : string {
        $tmpMatches = array_slice($matches, 1);
        $tmpMatches = array_filter($tmpMatches, function ($match) {
           return $match != '' || !str_contains($match, ':');
        });

        $content = reset($tmpMatches);
        $arguments = [];
        if ($this->tagArguments > 0 || $this->unknownArgumentsCount) {
            $tempArgumentsBuffer = array_values(array_slice($tmpMatches, 2));
            foreach ($tempArgumentsBuffer as $argument) {
                $arguments[] = $argument;
            }
        }
        $this->validateContent($content);
        $this->validateArguments($arguments);

        return ($this->processingFunction)($content, $arguments);
    }

    private static function collectAndReturn(ServiceTag $tag) : ServiceTag {
        TagAgent::addToServiceTagsContainer($tag);
        return $tag;
    }

    public static final function create(string $tagName, $handleFunction = null, $availableArguments = null, $requireArguments = null) : ServiceTag {
        $result = new ServiceTag($tagName, $handleFunction, $availableArguments, $requireArguments);

        return self::collectAndReturn($result);
    }

    public function __construct(string $tagName, callable $handlerFunction, int $availableArguments = null, int $requireArguments = null) {
        if (!preg_match('/' . self::NAME_CONTENT . '/ms', $tagName)) {
            throw new TagError("", ErrorManager::EC_INVALID_TAG_NAME);
        }
        if ($requireArguments > $availableArguments) {
            throw new TagError("", ErrorManager::EC_REQUIRED_ATTRIBUTES_COUNT_MORE_THEN_AVAILABLE);
        }

        $tagNamePattern = preg_quote($tagName);
        $tagContent     = self::CUSTOM_BODY;

        $this->tagName               = $tagName;
        $this->tagArguments          = $availableArguments ?? 0;
        $this->tagRequireArguments   = $requireArguments ?? 0;
        $this->processingFunction    = $handlerFunction;
        $this->tagPattern            = '/\{' . $tagNamePattern . '\|' . $tagContent;

        if ($this->tagArguments > 0) {
            $this->tagPattern .= ':';
            for ($i = 1 ; $i <= $this->tagArguments ; $i++) {
                if ($i <= $this->tagRequireArguments) {
                    $this->tagPattern .= self::ARGUMENT_DEFINED . ',';
                } else {
                    $this->tagPattern .= self::ARGUMENT_AVAILABLE . ',';
                }
            }
            $this->tagPattern = trim($this->tagPattern, ',');
        }
        $this->tagPattern .= '\}/ms';
    }

    public final function execute(string $inWhat = null, int &$replacingCount = 0) : string {
        $plainText = is_null($inWhat) ? ob_get_contents() : $inWhat;

        if ($this->unknownArgumentsCount) {
            $pattern = '/\{' . preg_quote($this->tagName) . '\|' . self::CUSTOM_BODY . self::CUSTOM_ARGUMENT . '\}/ms';
        } else {
            $pattern = $this->tagPattern;
        }

        $originalPlainText = $plainText;
        while (preg_match_all($pattern, $plainText) > 0) {
            $plainText = preg_replace_callback($pattern, [$this, 'replaceByPattern'], $plainText, -1, $replacingCount);
            if ($originalPlainText == $plainText) {
                throw new TagError("", ErrorManager::EC_TAG_COMPILATION_HAS_NO_RESULT, [$this->tagName]);
            }
        }

        return $plainText;
    }

    public function setProcessingFunction(callable $fc) : ServiceTag {
        $this->processingFunction = $fc;

        return $this;
    }

    public function setUnknownCountOfArguments() : ServiceTag {
        $this->unknownArgumentsCount = true;

        return $this;
    }

    public function getName() : string {
        return $this->tagName;
    }

    public function getPattern() : string {
        return $this->tagPattern;
    }

    public function getCustomPattern() : string {
        $tagName = preg_quote($this->tagName);
        $tagBody = self::BODY_CONTENT;
        $tagArguments = self::CUSTOM;

        if ($this->unknownArgumentsCount) {
            $tagBody .= ':';
        } elseif ($this->tagArguments > 0) {
            $tagBody .= '(:';
            $tagArguments .= ')';
        }

        return "/\{$tagName\|{$tagBody}{$tagArguments}\}/ms";
    }
}