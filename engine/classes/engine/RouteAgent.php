<?php

namespace Engine;

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Engine\Services\Route;
use Exceptions\Exemplars\DublicatedRouteError;
use Exceptions\Exemplars\InvalidHttpRequestTypeError;
use Exceptions\Exemplars\InvalidRouteHandlerFunction;
use Exceptions\Exemplars\InvalidRouteIdentificatorError;
use Exceptions\TavernException;
use Guards\Logger;
use Users\Models\User;
use Users\UserAgent;

class RouteAgent
{
    private const ROUTES_MAIN       = "engine/customs/routes/main.php";
    private const ROUTES_PROFILE    = "engine/customs/routes/profile.php";
    private const ROUTES_ADMINPANEL = "engine/customs/routes/adminpanel.php";
    public const  REDIRECT_ABSOLUTE = 1;
    public const  REDIRECT_RELATIVE = 2;

    private static $hasRegisteredAsAgent    = false;
    private static $routes                  = [
        'get'  => [],
        'post' => [],
    ];
    private static $routesByNameSubscripted = [];
    private static $justContentReturnRoutes = [];
    private static $currentPrefix           = null;
    private static $currentHandler          = null;
    private static $currentRunCondition     = null;
    private static $currentAccessCondition  = null;

    private static function withHandler(string $moduleFile, callable $fn) {
        if (!file_exists(HOME_ROOT . $moduleFile)) {
            throw new TavernException("Module file doesn't find", ErrorManager::EC_FILE_NOT_EXIST);
        }

        self::$currentHandler = $moduleFile;
        $fn();
        self::$currentHandler = null;
    }

    private static function execute(Route $route) {
        if ($route->hasUnavailableParameter()) {
            // @todo: сделать перенаправление на отдельную страницу 400 ошибки (недопустимые параметры)
            http_response_code(400);
            echo 'Bad request (route execute)';
            exit(0);
        }

        if (!$route->callHandler()) {
            throw new InvalidRouteHandlerFunction("", ErrorManager::EC_INVALID_ROUTE_HANDLER_RESULT, [$route->getName() != '' ? $route->getName() : $route->getUrl()]);
        }
    }

    private static function addServiceRoutes() {
        Route::create("get", "libs/[path]", function (array $args) {
            switch (BuildManager::getFileExtension("libs/{$args['path']}")) {
                case "js":
                case "min.js":
                    header("Content-Type: application/javascript");
                    break;
                case "css":
                case "min.css":
                    header("Content-Type: text/css");
                    break;
                case "png":
                    header("Content-Type: image/png");
                    break;
                case "gif":
                    header("Content-Type: image/gif");
                    break;
                case "jpg":
                    header("Content-Type: image/jpg");
                    break;
                case "jpeg":
                    header("Content-Type: image/jpeg");
                    break;
            }
            return BuildManager::includeContent("libs/{$args["path"]}");
        })->setName("libs-path");

        Route::create("get", "css/main/{cssname}", function (array $args) {
            header("Content-Type: text/css");
            return BuildManager::includeContentFromTemplate("css/{$args['cssname']}");
        })->setName("css-main");
        Route::create("get", "css/main/errors/{cssname}", function (array $args) {
            header("Content-Type: text/css");
            return BuildManager::includeContentFromTemplate("errors/{$args['cssname']}");
        })->setName("template-error-css");
        Route::create("get", "css/main/uploader/{cssname}", function (array $args) {
            header("Content-Type: text/css");
            return BuildManager::includeContentFromTemplate("uploader/{$args['cssname']}");
        })->setName("template-uploader-css");
        Route::create("get", "js/main/{jsmain}", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContentFromTemplate("js/{$args["jsmain"]}");
        })->setName("template-js");
        Route::create("get", "js/main/errors/[jsmain]", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContentFromTemplate("errors/" . $args["jsmain"]);
        })->setName("template-error-js");
        Route::create("get", "js/main/uploader/{jsmain}", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContentFromTemplate("uploader/{$args["jsmain"]}");
        })->setName("template-uploader-js");
        Route::create("get", "images/main/{imagename}", function (array $args) {
            header("Content-Type: image/*");
            return BuildManager::includeContentFromTemplate("css/{$args['imagename']}");
        })->setName("css-images");

        Route::create("get", "js/default/{jsmain}", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContent("site/scripts/js/" . $args["jsmain"]);
        })->setName("js-default");

        Route::create("get", "css/adminpanel/{cssname}", function (array $args) {
            header("Content-Type: text/css");
            return BuildManager::includeContentFromAdminpanelTemplate("css/{$args["cssname"]}");
        });
        Route::create("get", "js/adminpanel/{jsname}", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContentFromAdminpanelTemplate("js/{$args["jsname"]}");
        });

        Route::create("get", "css/profile/{cssname}", function (array $args) {
            header("Content-Type: text/css");
            return BuildManager::includeContentFromTemplate("profile/{$args["cssname"]}");
        })->setName("css-profile");
        Route::create("get", "js/profile/{jsname}", function (array $args) {
            header("Content-Type: application/javascript");
            return BuildManager::includeContentFromTemplate("profile/{$args["jsname"]}");
        })->setName("js-profile");

        Route::create("get", "images/icon", function () {
            header("Content-Type: image/x-icon");
            return BuildManager::includeContentFromTemplate('icon.ico');
        })->setName("site-icon");
        Route::create("get", "favicon.ico", function () {
            header("Content-Type: image/x-icon");
            return BuildManager::includeContentFromTemplate('icon.ico');
        })
             ->setName("favicon-url");
        Route::create("get", "images/{banner}", function (array $args) {
            header("Content-Type: image/*");
            return BuildManager::includeContentFromTemplate($args["banner"]);
        })->setName("banners");

        Route::create("get", "users/avatars/{user_id}", function (array $args) {
            $user = new User($args['user_id']);

            header("Content-Type: image/*");
            return BuildManager::includeContent("uploads/avatars/{$user->getAvatar()}");
        })->setName("user-avatar");
        Route::create("get", "uploads/images/{imagename}", function (array $args) {
            header("Content-Type: image/*");
            return BuildManager::includeContent("uploads/images/{$args['imagename']}");
        })->setName("uploaded-images");
        Route::create("get", "/", MAIN_MODULE)
             ->setTitling(LanguageManager::GetTranslation("home"))
             ->setName("main-page");
        Route::create("get", "/profile", PROFILE_MODULE)
             ->setTitling(function () {
                 if (UserAgent::isAuthorized()) {
                     return LanguageManager::GetTranslation("my-profile");
                 }
                 else {
                     return LanguageManager::GetTranslation("profile");
                 }
             })
             ->setName("profile-page");
        Route::create("get", "/adminpanel", ADMINPANEL_MODULE)->setName("adminpanel-page");

        self::$justContentReturnRoutes = array_keys(self::$routes['get']);

        self::withHandler(MAIN_MODULE, function () {
            BuildManager::include(self::ROUTES_MAIN);
        });
        self::withHandler(PROFILE_MODULE, function () {
            self::usingPrefix("profile", function () {
                BuildManager::include(self::ROUTES_PROFILE, true);
            });
        });
        self::withHandler(ADMINPANEL_MODULE, function () {
            self::usingPrefix("adminpanel", function () {
                BuildManager::include(self::ROUTES_ADMINPANEL);
            });
        });
    }

    private static function getAssociatedUrlList(string $method = 'get') : array {
        $urls = [];

        if (is_null($method)) {
            foreach (self::$routes as $methodContainer) {
                foreach ($methodContainer as $route) {
                    $urls[] = $route->getUrl();
                }
            }
        }
        else {
            foreach (self::$routes[$method] as $route) {
                $urls[] = $route->getUrl();
            }
        }

        return $urls;
    }

    private static function getAssociatedNameList(string $method = 'get') : array {
        $routesWithNames = [];

        if (is_null($method)) {
            foreach (self::$routes as $methodContainer) {
                foreach ($methodContainer as $route) {
                    if ($route->getName() == "") {
                        continue;
                    }

                    $routesWithNames[] = $route->getName();
                }
            }
        }
        else {
            foreach (self::$routes[$method] as $route) {
                if ($route->getName() == "") {
                    continue;
                }

                $routesWithNames[] = $route->getName();
            }
        }

        return $routesWithNames;
    }

    protected final static function addRouteToNamesContainer(Route $route) {
        if (in_array($route->getName(), array_keys(self::$routes[$route->getMethod()])) && $route->getName() != null) {
            throw new DublicatedRouteError("", ErrorManager::EC_DUPLICATED_ROUTE_NAME);
        }

        if ($route->getName() != null) {
            self::$routesByNameSubscripted[$route->getName()] = $route;
        }
    }

    protected final static function addRoute(Route $route) {
        if (!in_array($route->getMethod(), ['get', 'post', 'delete', 'put'])) {
            throw new InvalidHttpRequestTypeError();
        }

        $urls  = self::getAssociatedUrlList($route->getMethod());
        $names = self::getAssociatedNameList($route->getMethod());
        if (in_array($route->getUrl(), $urls)) {
            throw new DublicatedRouteError("", ErrorManager::EC_DUPLICATED_ROUTE_URL);
        }
        if (in_array($route->getName(), $names)) {
            throw new DublicatedRouteError("", ErrorManager::EC_DUPLICATED_ROUTE_NAME);
        }

        self::$routes[$route->getMethod()][] = $route;
    }

    public final static function getCurrentHandler() : string {
        return self::$currentHandler;
    }

    public final static function getRouteByName(string $routeName) : Route {
        if (!isset(self::$routesByNameSubscripted[$routeName])) {
            throw new InvalidRouteIdentificatorError("", ErrorManager::EC_INVALID_ROUTE_NAME, [$routeName]);
        }
        else {
            return self::$routesByNameSubscripted[$routeName];
        }
    }

    public final static function registerRoutes() : bool {
        if (self::$hasRegisteredAsAgent) {
            throw new TavernException("Route agent must be registered only one time");
        }

        self::addServiceRoutes();

        self::$hasRegisteredAsAgent = true;

        return !empty(self::$routes);
    }

    public final static function parseUrl() {
        $currentRoute = self::getCurrentRoute();

        if ($currentRoute === false) {
            http_response_code(404);
            echo "Not found (parseUrl)";
            exit(1);
        }

        self::execute($currentRoute);
    }

    public static function usingPrefix(string $prefix, callable $runWithPrefix) {
        self::$currentPrefix = $prefix;
        $runWithPrefix($prefix);
        self::$currentPrefix = null;
    }

    public static function usePrefix(string $prefix) {
        self::$currentPrefix = $prefix;
    }

    public static function getPrefix() {
        return self::$currentPrefix;
    }

    public static function hasPrefix() : bool {
        return !is_null(self::$currentPrefix);
    }

    public static function resetPrefix() {
        self::$currentPrefix = null;
    }

    public static function hasRunCondition() : bool {
        return !is_null(self::$currentRunCondition);
    }

    public static function withRunCondition(callable $conditionFn, callable $creatingFn) {
        self::$currentRunCondition = $conditionFn;
        $creatingFn();
        self::$currentRunCondition = null;
    }

    public static function getRunCondition() : callable {
        return self::$currentRunCondition;
    }

    public static function hasAccessCondition() : bool {
        return !is_null(self::$currentAccessCondition);
    }

    public static function withAccessCondition(callable $accessConditionFn, callable $creatingFn) {
        self::$currentAccessCondition = $accessConditionFn;
        $creatingFn();
        self::$currentAccessCondition = null;
    }

    public static function getAccessCondition() : callable {
        return self::$currentAccessCondition;
    }

    public static function redirect(string $path, int $flag = self::REDIRECT_RELATIVE) {
        switch ($flag) {
            case self::REDIRECT_ABSOLUTE:
                header("Location: $path");
                exit;
            case self::REDIRECT_RELATIVE:
                header("Location: /$path");
                exit;
        }
    }

    public static function getCurrentRoute() : Route|false {
        $currentMethod            = mb_strtolower($_SERVER["REQUEST_METHOD"]);
        $currentUrlWithoutSlashes = trim($_SERVER["REQUEST_URI"], '\/');

        /** @var Route $route Checking route entity */
        foreach (self::$routes[$currentMethod] as $route) {
            if ($route->isItLookingForMe($currentUrlWithoutSlashes)) {
                return $route;
            }
        }

        return false;
    }

    public static function buildRoute(string $name, $chainLinks = '') : string {
        if (is_string($chainLinks) && strlen($chainLinks) == 0) {
            $chainLinks = [];
        }

        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . self::getRouteByName($name)->getUrlWithReplacing($chainLinks);
    }
}