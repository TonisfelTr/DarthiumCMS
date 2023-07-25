<?php

namespace Builder\Services;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Engine\ErrorManager;
use Exceptions\Exemplars\TagError;
use Exceptions\TavernException;
use Builder\Parser\HtmlDOM;
use Guards\Logger;

class HtmlTag extends Tag
{
    private const TAG_NAME_PATTERN         = '/([a-zA-Z\:]+)/ms';
    private const ARGUMENT_NAME_PATTERN    = '([a-zA-Z\-]+)';
    private const ARGUMENT_CONTENT_PATTERN = '([a-zA-Z0-9_\-\!\@\$\%\&\*\(\)\[\]\{\}\=\+\\\\\|\/\s\'\:\<\>\?\,\.]+)';

    private array  $availableAttributes      = [];
    private array  $requiredAttributes       = [];
    private bool   $withoutClosing           = false;
    private        $resultClosingTagFunction = null;
    protected      $processingFunction;
    protected bool $tagOnlyForOneCompilation = false;

    private static function collectAndReturn(HtmlTag $tag) {
        TagAgent::addToHtmlTagsContainer($tag);
        return $tag;
    }

    public final static function create(string $tagName, $availableAttributes = [], $requiredAttributes = []) : HtmlTag {
        $result = new HtmlTag($tagName, $availableAttributes, $requiredAttributes);

        return HtmlTag::collectAndReturn($result);
    }

    protected final function __construct(string $tagName, array $availableAttributes = [], array $requiredAttributes = []) {
        if (count($availableAttributes) < count($requiredAttributes)) {
            throw new TavernException("", ErrorManager::EC_REQUIRED_ATTRIBUTES_COUNT_MORE_THEN_AVAILABLE);
        }
        if (!preg_match(self::TAG_NAME_PATTERN, $tagName)) {
            throw new TagError("", ErrorManager::EC_INVALID_TAG_NAME);
        }

        $this->withoutClosing           = false;
        $this->tagName                  = $tagName;
        $this->availableAttributes      = $availableAttributes;
        $this->requiredAttributes       = $requiredAttributes;
        $this->resultClosingTagFunction = function ($content, $attributes) {
            return '<' . '?' . 'php' . ' } ' . '?' . '>';
        };
    }

    public function execute(string $inWhat = null) : string {
        $plainText  = is_null($inWhat) ? ob_get_contents() : $inWhat;
        $htmlObject = new HtmlDOM();
        $htmlObject->load($plainText);

        $tagEntities = $htmlObject->find($this->tagName);
        //Получить полный код;
        //Увидеть все with-теги;
        //Посмотреть, какие не обрабатываются.
        if (!empty($tagEntities)) {
            foreach ($tagEntities as $tagEntity) {
                $attributes = [];
                foreach ($tagEntity->attr as $attributeName => $attributeValue) {
                    $attributes[$attributeName] = $attributeValue;
                }

                $replacingTagContent = BuildManager::hardCompile(PHP_EOL . $tagEntity->innertext);
                $replacingStartTag   = ($this->processingFunction)($replacingTagContent, $attributes);
                $replacingEndTag     = !$this->withoutClosing ? ($this->resultClosingTagFunction)($replacingTagContent, $attributes) : '';

                $tagEntity->outertext = "{$replacingStartTag}{$replacingTagContent}{$replacingEndTag}";
            }

            $result = $htmlObject->save();
        }
        else {
            $result = $plainText;
        }

        return $result;
    }

    public function setProcessingFunction(callable $fc) : HtmlTag {
        $this->processingFunction = $fc;

        return $this;
    }

    public function setResultClosingTagAction(callable $closingTagAction) : HtmlTag {
        $this->resultClosingTagFunction = $closingTagAction;

        return $this;
    }

    public function setWithoutClosing() : HtmlTag {
        $this->withoutClosing = true;

        return $this;
    }
}