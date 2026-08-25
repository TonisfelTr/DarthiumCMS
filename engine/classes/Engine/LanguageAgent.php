<?php

namespace Engine;

use Exceptions\Exemplars\LanguageFileNotFoundError;

class LanguageAgent
{
    private static $languageArray = [];
    
    public static function translateBoolean(bool $bool): string {
        return ($bool) ? LanguageAgent::getTranslation("yes") : LanguageAgent::getTranslation("no");
    }
    
    /**
     * Include language file to project.
     *
     * @return mixed
     */
    public static function load() {
        if (Engine::GetEngineInfo("sl") == "")
            $languageFile = LANGUAGE_ROOT . "English.php";
        else
            $languageFile = LANGUAGE_ROOT . Engine::GetEngineInfo("sl") . ".php";
        if (!file_exists($languageFile))
            throw new LanguageFileNotFoundError("Language file does not exist", 12);

        require $languageFile;
        self::$languageArray = $languagePack;
    }


    /** Return translated value from language dictionary by path.
     * @param string $path
     * @return string
     */
    public static function getTranslation(string $path, ...$vars) {
        if (isset(self::$languageArray[$path]) && !is_array(self::$languageArray[$path])) {
            $think = self::$languageArray[$path];
        } else {
            $path = trim($path);

            $exploded = explode(".", $path);

            if (end($exploded) == "")
                return $path;

            $think = null;
            for ($i = 0; $i < count($exploded); $i++) {
                if (empty($think)) {
                    //If $think is empty set it into var.
                    $think = self::$languageArray[$exploded[$i]];
                } else {
                    if (is_array($think)) {
                        $think = $think[$exploded[$i]];
                    } elseif (!str_contains($think, '{') && !str_contains($think, '}')) {
                        return $think;
                    }
                }
            }
        }
        
        if (!empty($think)) {
            $time = 0;
            foreach ($vars as $var) {
                $time += 1;
                $param = '{' . $time . '}';
                $think = str_replace($param, $var, $think);
            }
            return $think;
        } else
            return $path;
    }

}