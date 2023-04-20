<?php

namespace Builder\Services;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Engine\ErrorManager;
use Exceptions\Exemplars\TagError;
use Exceptions\TavernException;

class Tag extends TagAgent
{
    private const NAME_PATTERN = '/\s*\{[a-zA-Z_\:\-]+\}\s*/ms';

    protected $processingFunction;
    protected bool $onlyForOneCompilation = false;
    protected bool $compiled = false;
    protected string $tagName;

    private static function collectAndReturn(Tag $tag, bool $strict = true) {
        TagAgent::addToTagsContainer($tag, $strict);
        return $tag;
    }

    public static function create(string $name, bool $forOneCompilation = false, bool $withValidation = true) : Tag {
        $result = new Tag($name, $forOneCompilation);

        return Tag::collectAndReturn($result, $withValidation);
    }

    private function __construct(string $name, bool $forOneCompilation = false) {
        $this->tagName = trim($name);
        $this->onlyForOneCompilation = $forOneCompilation;

        if (!preg_match(self::NAME_PATTERN, $name)) {
            throw new TagError("", ErrorManager::EC_INVALID_TAG_NAME);
        }

        $this->processingFunction = function () { return ''; };
    }

    public function execute(string $inWhat = null) : string {
        $inWhat = is_null($inWhat) ? ob_get_contents() : $inWhat;

        if ($this->isOnlyForOneCompliation() && $this->compiled) {
            return $inWhat;
        }
        if (!str_contains($inWhat, $this->tagName)) {
            return $inWhat;
        }

        $originalInWhat = $inWhat;
        if ($this->isOnlyForOneCompliation()) {
            $inWhat = BuildManager::replaceOnceInString($this->tagName, ($this->processingFunction)(), $inWhat);
            $inWhat = str_replace($this->tagName,
                                  htmlentities($this->tagName),
                                  $inWhat);
            $this->compiled = true;
        } else {
            $inWhat = str_replace($this->tagName,
                                  ($this->processingFunction)(),
                                  $inWhat,
                                  TagAgent::$replacingCount["system"][$this->getName()]);
        }

        if ($inWhat === $originalInWhat) {
            throw new TagError("", ErrorManager::EC_TAG_COMPILATION_HAS_NO_RESULT, [$this->tagName]);
        }

        return $inWhat;
    }

    public function isOnlyForOneCompliation() : bool {
        return $this->onlyForOneCompilation;
    }

    public function isCompiled() : bool {
        return $this->compiled;
    }

    public function getName() : string {
        return $this->tagName;
    }

    public function getPattern() : string {
        return self::NAME_PATTERN;
    }

    public function setProcessingFunction(callable $fc) : Tag {
        $functionResultForTypeChecking = $fc();
        $functionResultType = gettype($functionResultForTypeChecking);

        if ($functionResultType != "string") {
            throw new TagError("", ErrorManager::EC_INVALID_RETURN_TYPE_OF_TAG_PROCESSING, [$functionResultType, $this->tagName]);
        }

        $this->processingFunction = $fc;

        return $this;
    }
}