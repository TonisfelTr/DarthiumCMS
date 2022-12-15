<?php

namespace Users\Services;

use Engine\Engine;
use Exceptions\Exemplars\FlashSessionDidNotReadError;
use Exceptions\Exemplars\SessionNotContainError;

class FlashSession
{
    public const MA_DEFAULT = 0;
    public const MA_ERRORS = 1;
    public const MA_INFOS = 2;
    public const MA_WARNINGS = 4;

    private static $hadBeenRead = false;
    private static $default = [];
    private static $errors = [];
    private static $infos = [];
    private static $warnings = [];

    private static function selfDecode() {
        self::$hadBeenRead = true;

        self::$default = json_decode($_SESSION["d"], true) ?? [];
        self::$errors = json_decode($_SESSION["e"], true) ?? [];
        self::$infos = json_decode($_SESSION["i"], true) ?? [];
        self::$warnings = json_decode($_SESSION["w"], true) ?? [];
    }

    private static function removeMessage($key, int $messageContainerIdentifier) {
        if (!self::$hadBeenRead) {
            throw new FlashSessionDidNotReadError();
        }

        switch ($messageContainerIdentifier) {
            case self::MA_ERRORS:

            case self::MA_INFOS:
                $messageArray = json_decode($_SESSION["i"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }

                return $messageArray[$key];
            case self::MA_WARNINGS:
                $messageArray = json_decode($_SESSION["w"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }

                $result = $messageArray[$key];

                return $result;
            default:
            case self::MA_DEFAULT:
                $messageArray = json_decode($_SESSION["d"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }

                return $messageArray[$key];
        }
    }

    private static function get($key, int $messageContainerIdentifier)  {
        switch ($messageContainerIdentifier) {
            case self::MA_ERRORS:
                $messageArray = json_decode($_SESSION["e"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }
                self::removeMessage($key, $messageContainerIdentifier);

                $result = $messageArray[$key];
                return $result;
            case self::MA_INFOS:
                $messageArray = json_decode($_SESSION["i"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }
                self::removeMessage($key, $messageContainerIdentifier);

                $result = $messageArray[$key];
                return $result;
            case self::MA_WARNINGS:
                $messageArray = json_decode($_SESSION["w"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }
                self::removeMessage($key, $messageContainerIdentifier);
                $result = $messageArray[$key];

                return $result;
            default:
            case self::MA_DEFAULT:
                $messageArray = json_decode($_SESSION["d"], true);

                if (!isset($messageArray[$key])) {
                    throw new SessionNotContainError("This key does not exist in flash container");
                }
                self::removeMessage($key, $messageContainerIdentifier);

                $result = $messageArray[$key];
                return $result;
        }
    }

    private static function selfEncode() {
        self::$default = json_encode(self::$default);
        self::$errors = json_encode(self::$errors);
        self::$infos = json_encode(self::$infos);
        self::$warnings = json_encode(self::$warnings);

        self::$hadBeenRead = false;
    }

    public static function SetUpSource() {
        ini_set("session.cookie_lifetime", 31536000);
        ini_set("session.gc_maxlifetime", 31536000);

        session_id($_COOKIE["PHPSESSID"] ?? hash("sha1", Engine::RandomGen(16)));
        session_start();
    }

    public static function writeIn($value, int $messageContainerIdentifier, $key = null) {
        self::selfDecode();

        switch ($messageContainerIdentifier) {
            case self::MA_ERRORS:
                if (is_null($key)) {
                    self::$errors[$key] = $value;
                } else {
                    self::$errors[] = $value;
                }

                break;
            case self::MA_INFOS:
                if (is_null($key)) {
                    self::$infos[$key] = $value;
                } else {
                    self::$infos[] = $value;
                }

                break;
            case self::MA_WARNINGS:
                if (is_null($key)) {
                    self::$warnings[$key] = $value;
                } else {
                    self::$warnings[] = $value;
                }

                break;
            default:
            case self::MA_DEFAULT:
                if (is_null($key)) {
                    self::$default[$key] = $value;
                } else {
                    self::$default[] = $value;
                }

                break;
        }

        self::selfEncode();
    }

    public static function readFrom( $key, int $messageContainerIdentifier) {
        $result = self::get($key, $messageContainerIdentifier);

        self::removeMessage($key, $messageContainerIdentifier);

        return $result;
    }

    public static function readFromErrors( $key) {
        return self::get($key, self::MA_ERRORS);
    }

    public static function readFromInfo( $key) {
        return self::get($key, self::MA_INFOS);
    }

    public static function readFromWarnings( $key) {
        return self::get($key, self::MA_WARNINGS);
    }

    public static function writeInSystemContainer($value,  $key = null) {
        $array = json_decode($_SESSION["s"], true);

        if (!is_null($key)) {
            $array[$key] = $value;
        } else {
            $array[] = $value;
        }

        $_SESSION["s"] = json_encode($array);
    }

    public static function readFromSystemContainer( $key) {
        return json_decode($_SESSION["s"], true)[$key];
    }

    public static function getSessionId() {
        return session_id();
    }
}