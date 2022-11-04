<?php

namespace Engine;

use Exceptions\Exemplars\LanguageFileNotFoundError;

class LanguageManager
{
    private static $languageArray = [];

    /**
     * Include language file to project.
     *
     * @return mixed
     */
    public static function load()
    {
        if (Engine::GetEngineInfo("sl") == "")
            $languageFile = $_SERVER["DOCUMENT_ROOT"] . "/languages/English.php";
        else
            $languageFile = $_SERVER["DOCUMENT_ROOT"] . "/languages/" . Engine::GetEngineInfo("sl") . ".php";
        if (!file_exists($languageFile))
            throw new LanguageFileNotFoundError("Language file does not exist", 12);

        require $languageFile;
        self::$languageArray = $languagePack;
    }


    /** Return translated value from language dictionary by path.
     * @param string $path
     * @return string
     */
    public static function GetTranslation(string $path, ...$vars)
    {
        if (isset(self::$languageArray[$path]) && !is_array(self::$languageArray[$path]))
            return self::$languageArray[$path];

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
                } else {
                    return $think;
                }
            }
        }
        if (!empty($think)) {
            $time = 0;
            foreach ($vars as $var) {
                $time++;
                $param = "{" . $time . "}";
                $think = str_ireplace($param, $var, $think);
            }
            return $think;
        } else
            return $path;
    }

}