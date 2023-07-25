<?php

namespace Engine\Services;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Engine\ErrorManager;
use Engine\RouteAgent;
use Exceptions\Exemplars\InvalidRouteConditionError;
use Exceptions\Exemplars\InvalidRouteIdentificatorError;
use Exceptions\Exemplars\InvalidRouteUrlChainLinkError;
use Exceptions\Exemplars\UnavailabledRouteParameterError;
use Exceptions\TavernException;

class Route extends RouteAgent
{
    private string $url;
    private array  $urlArguments         = [];
    private array  $urlArgumentsNames    = [];
    private array  $urlChainLinksContent = [];

    private string $method;
    private string $name                                = '';
    private string $title                               = '';
    private array  $availableParameters                 = [];
    private string $handlerName                         = '';
    private        $handlerFunction                     = false;
    private        $parameters                          = [];
    private        $runConditionFunction;
    private        $accessConditionFunction;
    private        $titlingFunction;
    private        $customPageWithConditionFailed       = false;
    private        $customPageWithAccessConditionFailed = false;
    private        $justIncludingRoute                  = false;

    public static function createGet(string $url, string $handlerName) : Route {
        return self::create('get', $url, $handlerName);
    }

    public static function createPost(string $url, string $handlerName) : Route {
        return self::create('post', $url, $handlerName);
    }

    public static function createPut(string $url, string $handlerName) : Route {
        return self::create('put', $url, $handlerName);
    }

    public static function createDelete(string $url, string $handlerName) : Route {
        return self::create('delete', $url, $handlerName);
    }

    public static function create(string $httpMethod, string $url, $handler = null) : Route {
        $url = trim($url, '\/');

        $newRoute = new self($httpMethod, $url, $handler);

        RouteAgent::addRoute($newRoute);
        return $newRoute;
    }

    private function __construct(string $httpMethod, string $url, $handler = null) {
        $this->method          = $httpMethod;
        $this->url             = $url;
        $this->titlingFunction = function () {
            return '';
        };
        $handler               = is_null($handler) ? RouteAgent::getCurrentHandler() : $handler;

        if (!preg_match("/[a-zA-Z0-9_\-\{\}\:\[\]\,]+/", $this->url) && $this->getUrl() != "") {
            throw new InvalidRouteIdentificatorError("", ErrorManager::EC_INVALID_SYMBOLS_IN_ROUTE_URL);
        }

        if (is_string($handler)) {
            BuildManager::fileExists($handler, HOME_ROOT, true);

            $this->handlerName = $handler;
        }
        elseif (is_callable($handler)) {
            $this->handlerFunction = $handler;
        }
        else {
            $currentType = gettype($handler);

            throw new TavernException("Argument #3 must be string or callable, {handler_type_result} given",
                                      ErrorManager::EC_INVALID_ROUTE_HANDLER_RESULT,
                                      [$currentType]);
        }

        $explodedUrl = array_filter(explode("/", $this->url), function (string $link) {
            if (trim($link) == '' || empty($link)) {
                return false;
            }

            return true;
        });
        $this->url   = implode('/', $explodedUrl);
        $this->url   = RouteAgent::hasPrefix() ? RouteAgent::getPrefix() . "/" . $url : $url;

        if (strpos($this->url, '{') || strpos($this->url, '[')) {
            foreach ($explodedUrl as $index => $urlChainLink) {
                $startsByBracket       = str_starts_with($urlChainLink, '{');
                $endsByBracket         = str_ends_with($urlChainLink, '}');
                $startsBySquareBracket = str_starts_with($urlChainLink, '[');
                $endsBySquareBracket   = str_ends_with($urlChainLink, ']');

                if ($startsByBracket && $endsByBracket) {
                    if (in_array($urlChainLink, array_keys($this->urlArguments))) {
                        throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_DUPLICATED_URL_CHAIN_LINK_NAME, [$url]);
                    }
                    if (!(preg_match("/\{[a-zA-Z0-9_\-]+\S{1,}\}/", $urlChainLink) ^ preg_match("/\[[a-zA-Z0-9_\-]+\S{1,}\]/", $urlChainLink))) {
                        throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_INVALID_URL_CHAIN_LINK_NAME, [$url]);
                    }

                    if (($colonPos = strpos($urlChainLink, ':')) !== false) {
                        $urlChainLinkName = '{' . substr($urlChainLink, 1, strpos($urlChainLink, ':') - 1) . '}';
                    }
                    else {
                        $urlChainLinkName = '{' . substr($urlChainLink, 1, -1) . '}';
                    }
                    $this->urlArgumentsNames[$urlChainLinkName] = $index;

                    $this->urlArguments["$index"] = new RouteUrlLink($urlChainLink, $index == count($explodedUrl) - 1);
                }
                elseif (($startsByBracket && !$endsByBracket) || (!$startsByBracket && $endsByBracket)) {
                    $chainLinkName = explode(":", trim($urlChainLink, "{}"))[0];
                    throw new InvalidRouteIdentificatorError("Invalid given property syntax for \"$chainLinkName\" chain link", ErrorManager::EC_INVALID_LINK_IN_URL);
                }

                if ($startsBySquareBracket && $endsBySquareBracket) {
                    $this->urlArguments["$index"] = new RouteUrlLink($urlChainLink, $index == count($explodedUrl) - 1, true);
                }
                elseif (($startsBySquareBracket && !$endsBySquareBracket) || (!$startsBySquareBracket && $endsBySquareBracket)) {
                    $chainLinkName = trim($urlChainLink, '[]');
                    throw new InvalidRouteIdentificatorError("Invalid given property syntax for \"$chainLinkName\" chain link", ErrorManager::EC_INVALID_LINK_IN_URL);
                }
            }
        }

        if (RouteAgent::hasRunCondition()) {
            $this->runConditionFunction = RouteAgent::getRunCondition();
        }
        if (RouteAgent::hasAccessCondition()) {
            $this->accessConditionFunction = RouteAgent::getAccessCondition();
        }
    }

    private function setValuesToURLChainsLinks() : void {
        $explodedCurrentUrl = explode("/", substr($_SERVER["REQUEST_URI"], 1));
        $explodedRouteUrl   = explode("/", $this->url);

        foreach ($explodedRouteUrl as $uclIndex => $uclContent) {
            if (str_starts_with($uclContent, '{')) {
                $currentArgument     = $this->urlArguments[$uclIndex];
                $currentArgumentName = $currentArgument->getName(RouteUrlLink::RL_TEXTNAME);

                if (!$currentArgument->canBeNull() && isset($explodedCurrentUrl[$uclIndex])) {
                    $this->urlChainLinksContent[$currentArgumentName] = $explodedCurrentUrl[$uclIndex];
                }
                elseif ($currentArgument->canBeNull()) {
                    if (isset($explodedCurrentUrl[$uclIndex])) {
                        $this->urlChainLinksContent[$currentArgumentName] = $explodedCurrentUrl[$uclIndex];
                    }
                    else {
                        $this->urlChainLinksContent[$currentArgumentName] = $currentArgumentName == 'page' ? 1 : null;
                    }
                }
            }
            elseif (str_starts_with($uclContent, '[')) {
                $currentArgument     = $this->urlArguments[$uclIndex];
                $currentArgumentName = $currentArgument->getName(RouteUrlLink::RL_TEXTNAME);

                $squareBracketPos                                 = strpos($this->url, '[');
                $contentForSquare                                 = substr(substr($_SERVER["REQUEST_URI"], 1), $squareBracketPos);
                $this->urlChainLinksContent[$currentArgumentName] = $contentForSquare;
            }
        }
    }

    protected function isItLookingForMe(string $url) : bool {
        /**
         * 1. Разбить на составляющие звенья полученного URL и установленного URL;
         * 2. Сопоставить константированные ячейки.
         * 3. Сопоставить ЗЦУ с текущим поглощением.
         * 4. Сопоставить ЗЦУ с полным замещением.
         * 5. Если кол-во ЗЦУ в полученном URL больше чем в установленном - ссылка 100% невалидна.
         */

        //1. Разбить на составляющие звенья полученного URL и установленного URL;
        $currentUCLs    = explode('/', $url);
        $routeUCLs      = explode('/', $this->url);
        $routeUCLsCount = count($routeUCLs);

        //2. Сопоставить...
        foreach ($currentUCLs as $uclIndex => $uclContent) {
            if ($uclIndex < $routeUCLsCount) {
                // ...  константированные ячейки.
                if (!str_starts_with($routeUCLs[$uclIndex], '{') && !str_starts_with($routeUCLs[$uclIndex], '[')) {
                    if ($routeUCLs[$uclIndex] != $uclContent) {
                        return false;
                    }
                }
                // 3. Сопоставить ЗЦУ с текущим поглощением.
                elseif (str_starts_with($routeUCLs[$uclIndex], '{')) {
                    if (!$this->urlArguments[$uclIndex]->isCorrect($uclContent)) {
                        return false;
                    }
                }
                // 4. Сопоставить ЗЦУ с полным замещением.
                elseif (str_starts_with($routeUCLs[$uclIndex], '[')) {
                    return true;
                }
            }
            // 5. Если кол-во ЗЦУ в полученном URL больше чем в установленном - ссылка 100% невалидна.
            else {
                return false;
            }
        }

        return true;
    }

    public function runCondition(array $urlChainLinkValues) : bool {
        if (isset($this->runConditionFunction) && is_callable($this->runConditionFunction)) {
            return (bool)($this->runConditionFunction)($urlChainLinkValues);
        }

        return true;
    }

    public function setCondition(callable $conditionFn) : Route {
        if (RouteAgent::hasRunCondition()) {
            throw new InvalidRouteConditionError(ErrorManager::getErrorDescription(57), 57);
        }

        $this->runConditionFunction = $conditionFn;

        return $this;
    }

    public function runAccessCondition(array $urlChainLinkValues) : bool {
        if (isset($this->accessConditionFunction) && is_callable($this->accessConditionFunction)) {
            return (bool)($this->accessConditionFunction)($urlChainLinkValues);
        }

        return true;
    }

    public function setAccessCondition(callable $conditionFn) : Route {
        if (RouteAgent::hasAccessCondition()) {
            throw new InvalidRouteConditionError(ErrorManager::getErrorDescription(58), 57);
        }

        $this->accessConditionFunction = $conditionFn;

        return $this;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getUrl() : string {
        return $this->url;
    }

    public function getName() {
        return $this->name ?: false;
    }

    public function setName(string $newName) : Route {
        if (!preg_match("/^[_\-a-zA-Z0-9]+$/", $newName) || strlen(trim($newName)) == 0) {
            throw new InvalidRouteIdentificatorError();
        }

        $this->name = $newName;
        RouteAgent::addRouteToNamesContainer($this);

        return $this;
    }

    public function hasUnavailableParameter() : bool {
        if (empty($this->availableParameters)) {
            return false;
        }

        $parameters = array_keys($_REQUEST);

        foreach ($parameters as $parameter) {
            if (!in_array($parameter, $this->availableParameters)) {
                return true;
            }
        }

        return false;
    }

    public function isParameterAvailable(string $parameterName) : bool {
        return in_array($parameterName, $this->availableParameters);
    }

    public function getAvailableParameters() : array {
        return $this->availableParameters;
    }

    public function getParameter(string $parameterName, bool $strict = false) : string {
        if (!$strict) {
            return $this->parameters[$parameterName] ?? false;
        }
        else {
            if (!isset($this->parameters[$parameterName]) || is_null($this->parameters[$parameterName])) {
                throw new UnavailabledRouteParameterError();
            }

            return $this->parameters[$parameterName] ?? false;
        }
    }

    public function setParameters(array $parameters) : Route {
        $this->availableParameters = $parameters;

        return $this;
    }

    public function isHandlerFunction() : bool {
        return !empty($this->handlerFunction);
    }

    public function isHandlerFile() : bool {
        return BuildManager::fileExists($this->handlerName);
    }

    public function isHandlerClass() : bool {
        BuildManager::fileExists($this->handlerName, HOME_ROOT, true);
        $tokens = token_get_all(HOME_ROOT . "{$this->handlerName}");

        $classInFile      = false;
        $closingTagInFile = false;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array(T_CLASS, $token)) {
                    $classInFile = true;
                }
                if (in_array(T_CLOSE_TAG, $token)) {
                    $closingTagInFile = true;
                }
            }
        }

        return $classInFile && !$closingTagInFile;
    }

    public function getHandler() {
        return !$this->isHandlerFunction() ? $this->handlerName : $this->handlerFunction;
    }

    public function showCustomPage(bool $forAccess = false) {
        BuildManager::turnOffOutputBuffering();

        if (!$forAccess) {
            http_response_code(404);
            if (!$this->customPageWithConditionFailed) {
                // @todo: Надо сделать страницу 404, если нужно отобразить ошибку в панели
                echo 'Not found (showCustomPage in panel)';
            }
            else {
                // @todo: Надо сделать страницу 404, если нужно отобразить ошибку как отдельную страницу.
                echo 'Not found (showCustomPage as page)';
            }
        }
        else {
            http_response_code(403);
            if (!$this->customPageWithAccessConditionFailed) {
                // @todo: Надо сделать страницу 403, если нужно отобразить ошибку в панели.
                echo 'Forbidden (showCustomPage in panel)';
            }
            else {
                // @todo: Надо сделать страницу 403, если нужно отобразить отдельную страницу.
                echo 'Forbidden (showCustomPage as page)';
            }
        }

        exit(0);
    }

    public function getUrlWithReplacing($content) : string {
        $url                 = $this->url;
        $writtenArguments    = $this->urlArguments;
        $this->urlArguments  = array_values($this->urlArguments);
        $lastChainLink       = end($this->urlArguments);
        $routeArgumentsCount = count($this->urlArguments);
        if (is_array($content) && $content) {
            $contentCount = count($content);
            if ($routeArgumentsCount == 1 && $lastChainLink->doesContainAllAfterIt()) {
                return str_replace($lastChainLink->getName(true), $content[0], $this->url);
            }
            else {
                foreach ($this->urlArguments as $index => $argument) {
                    /** Прыгаем по всем.
                     * 1. Если переданных параметров больше 1, аргументов больше 1, но передано меньше, чем нужно,
                     *    с учётом, что последний чейнлинк не может быть нулевым...
                     * 2. Если переданных параметров больше 1, аргументов больше 1, но передано меньше, чем нужно,
                     *    с учётом, что последний чейнлинк может быть нулевым...
                     */
                    if ($contentCount > 1 && $routeArgumentsCount > 1) {
                        if ($contentCount < $routeArgumentsCount && !$lastChainLink->canBeNull()) {
                            throw new InvalidRouteConditionError("", ErrorManager::EC_TOO_FEW_ROUTE_ARGUMENTS);
                        }
                        elseif ($contentCount < $routeArgumentsCount - 1 && $lastChainLink->canBeNull()) {
                            throw new InvalidRouteConditionError("", ErrorManager::EC_TOO_FEW_ROUTE_ARGUMENTS);
                        }
                    }
                    if (!isset($content[1]) && !($argument == $lastChainLink && $lastChainLink->doesContainAllAfterIt())) {

                    }
                    // Если последний чейнлинк - текущий и он может содержать всё до конца
                    if ($argument == $lastChainLink && $lastChainLink->doesContainAllAfterIt()) {
                        $url = str_replace($lastChainLink->getName(RouteUrlLink::RL_RECEIVEDNAME), end($content), $this->url);
                    }
                    elseif ($argument == $lastChainLink && !$lastChainLink->doesContainAllAfterIt() && count($content) == 1 && count($this->urlArguments) == 1) {
                        $url = str_replace($argument->getName(RouteUrlLink::RL_RECEIVEDNAME),
                                           str_starts_with($content[0], '$')
                                               ? '" ' . $content[0] . ' "'
                                               : $content[0],
                                           $url);
                    }
                    elseif (isset($lastContentPosition) && !isset($content[$index])) {
                        $url = substr($url, 0, $lastContentPosition);
                        break;
                    }
                    elseif (preg_match('/[a-zA-Z0-9\.\[\]\$\>\'\-]+/', $content[$index]) || $argument == $lastChainLink) {
                        $lastContentPosition = strpos($url, $argument->getName(RouteUrlLink::RL_RECEIVEDNAME)) + strlen($content[$index]);
                        $url                 = str_replace($argument->getName(RouteUrlLink::RL_RECEIVEDNAME),
                                                           str_starts_with($content[$index], '$')
                                                               ? '" ' . $content[$index] . ' "'
                                                               : $content[$index],
                                                           $url);
                    }
                    else {
                        throw new InvalidRouteUrlChainLinkError("",
                                                                ErrorManager::EC_INVALID_URL_CHAIN_LINK_CONTENT,
                                                                [$this->name, $argument->getName(RouteUrlLink::RL_RECEIVEDNAME)]);
                    }
                }
            }
        }
        elseif (is_string($content)) {
            if (preg_match('/[a-zA-Z0-9\.]+/', $content)) {
                $url = str_replace($lastChainLink->getName(true), $content, $url);
            }
        }

        $this->urlArguments = $writtenArguments;
        return $url;
    }

    public function linkExists(string $linkName) : bool {
        foreach ($this->urlArguments as $argument) {
            /** @var $argument RouteUrlLink */
            if ($argument->getName(RouteUrlLink::RL_TEXTNAME) == $linkName) {
                return true;
            }
        }

        return false;
    }

    public function callHandler() : bool {
        $this->setValuesToURLChainsLinks();

        $this->title = ($this->titlingFunction)($this->urlChainLinksContent) ?? '';

        if (!$this->runAccessCondition($this->urlChainLinksContent)) {
            http_response_code(403);
            echo "You don't have access to run that route. (route->callHandler())";
            exit(0);
        }
        if (!$this->runCondition($this->urlChainLinksContent)) {
            http_response_code(404);
            echo "Not found (route->callHandler())";
            exit(0);
        }

        if ($this->justIncludingRoute && !$this->isHandlerFunction()) {
            if (BuildManager::fileExists($this->handlerName)) {
                BuildManager::includeContent($this->handlerName);
                return true;
            }
            else {
                http_response_code(404);
                echo 'Not found (route callHandler)';
                exit(0);
                // @todo: Выкинуть 404 ошибку как отдельную страницу.
            }
        }
        if ($this->isHandlerClass() && !$this->justIncludingRoute) {
            $exploadHandlerString = explode("->", $this->handlerName);
            $handlerPath          = reset($exploadHandlerString);
            $handlerFunctionName  = end($exploadHandlerString);

            $handler = BuildManager::include("$handlerPath");
            call_user_func($handler->$handlerFunctionName, $this->urlArguments);
            return true;
        }
        if ($this->isHandlerFunction()) {
            $handlerResult = ($this->handlerFunction)($this->urlChainLinksContent);
            if ($handlerResult == false) {
                http_response_code(404);
                return false;
            }
            else {
                echo $handlerResult;
            }

            return true;
        }
        if ($this->isHandlerFile()) {
            BuildManager::fileExists($this->handlerName, HOME_ROOT, true);

            if (!TagAgent::isTagsRegistrationCompleted()) {
                TagAgent::registerSystemTags();
            }
            if (!TagAgent::isServiceTagsRegistrationCompleted()) {
                TagAgent::registerServiceTags();
            }
            if (!TagAgent::isHTMLTagsRegistrationCompleted()) {
                TagAgent::registerHtmlTags();
            }

            BuildManager::include($this->handlerName);

            return true;
        }

        return false;
    }

    public function setAvailableValuesOfChainLink(string $chainLinkName, array $values) : Route {
        if (!isset($this->urlArgumentsNames[$chainLinkName])) {
            throw new InvalidRouteUrlChainLinkError("", ErrorManager::EC_URL_CHAIN_LINK_DOES_NOT_EXIST, [$chainLinkName]);
        }

        $this->urlArguments[$this->urlArgumentsNames[$chainLinkName]]->setAvailableValues($values);
        return $this;
    }

    public function setTitling($titleObj) : Route {
        if (is_string($titleObj)) {
            $this->titlingFunction = function () use ($titleObj) : string {
                return $titleObj;
            };
        }
        elseif (is_callable($titleObj)) {
            $this->titlingFunction = $titleObj;
        }
        else {
            throw new InvalidRouteConditionError("", ErrorManager::EC_ROUTE_TITLING_INVALID_TYPE, [gettype($titleObj)]);
        }

        return $this;
    }

    public function setNotJustIncludingRoute() : Route {
        $this->justIncludingRoute = false;

        return $this;
    }

    public function getValueOfChainLink(string $chainLinkName) : string {
        $this->setValuesToURLChainsLinks();

        return $this->urlChainLinksContent[$chainLinkName];
    }

    public function getTitle() : string {
        return $this->title;
    }
}