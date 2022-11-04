<?php

namespace Engine;

use Exceptions\Exemplars\NotConnectedToDatabaseError;
use Exceptions\Exemplars\PluginError;
use Exceptions\Exemplars\PluginNotFoundError;
use Users\GroupAgent;

/**
 * Class PluginManager
 * @package Engine
 *
 *
 */
class PluginManager
{
    private static $plugins = [];
    private static $installed = [];
    private static $cssLines = [];
    private static $jsHeadLines = [];
    private static $jsFooterLines = [];

    private static function AddNavbarBtn(int $ofPlugin, string $type, string $content, int $parentId = 0, string $action = "") : int {
        return DataKeeper::InsertTo("tt_plugin_navbar", ["ofPlugin" => $ofPlugin,
            "type" => $type,
            "content" => $content,
            "action" => $action,
            "parent" => $parentId]);
    }

    public static function GetNavbarBtns(){
        $result = [];

        $btns = DataKeeper::Get("tt_plugin_navbar", ["*"], ["parent" => 0]);

        foreach ($btns as $btn) {
            $result[] = $btn;
        }

        return $result;
    }

    public static function GetNavbarListBtns(int $parentId){
        $result = [];

        $btns = DataKeeper::Get("tt_plugin_navbar", ["*"], ["parent" => $parentId]);

        foreach ($btns as $btn){
            $result[] = $btn;
        }

        return $result;
    }

    public static function CSSRegister(string $link){
        self::$cssLines[] = '<link href="'. $link . '" rel="stylesheet">' . PHP_EOL;
    }

    public static function JSHeadRegister(string $link){
        self::$jsHeadLines[] = '<script src="'. $link . '"></script>' . PHP_EOL;
    }

    public static function JSFooterRegister(string $link){
        self::$jsFooterLines[] = '<script src="'. $link . '"></script>' . PHP_EOL;
    }

    public static function GetPluginsList()
    {
        $root = scandir("addons/");
        $keymap = [];

        foreach ($root as $folder) {
            if ($folder == '.' && $folder == '..')
                continue;
            //Кеймапы - это меню на разных языках.
            $keymap[] = $folder;

        }

        foreach ($keymap as $hostFolder) {
            if (is_file(ADDONS_ROOT . "$hostFolder/bin/engine.php")) {
                $conf = include ADDONS_ROOT . "$hostFolder/config/config.php";
                if (!$conf) {
                    throw new PluginError("Config file of plugin \"$hostFolder\" does not exist");
                }
                self::$plugins[$hostFolder]["config"] = $conf;
            }
            continue;
        }
        return self::$plugins;
    }

    public static function InstallPlugin(string $name, string $codeName, string $description, int $status)
    {
        $insertInto = DataKeeper::InsertTo("tt_plugins", ["name" => $name, "codename" => $codeName, "description" => $description, "status" => $status]);
        if ($insertInto > 0) {
            self::$installed[$codeName] = ["name" => $name,
                "codeName" => $codeName,
                "description" => $description,
                "status" => $status];

            $lastId = $insertInto;
        }

        if (is_file( ADDONS_ROOT . "$codeName/config/traces.php")) {
            $traces = include_once ADDONS_ROOT . "$codeName/config/traces.php";

            foreach ($traces as $key => $value){
                DataKeeper::InsertTo("tt_plugin_trace", ["ofPlugin" => $lastId,
                    "system_text" => $key,
                    "system_text_to" => $value["page"],
                    "strict" => $value["strict"] ]);

            }
        }

        if (is_file(ADDONS_ROOT . "$codeName/config/permissions.php")){
            $permissions = include_once ADDONS_ROOT . "$codeName/config/permissions.php";

            foreach (GroupAgent::GetGroupList() as $group) {
                foreach ($permissions as $permission => $value) {
                    DataKeeper::InsertTo("tt_plugin_permissions", ["codename" => $permission,
                        "value" => $value["default_value"],
                        "ofGroup" => $group["id"],
                        "ofPlugin" => $lastId,
                        "translate_path" => $value["translate_path"]]);
                }
            }
        }

        if (is_file(ADDONS_ROOT . "$codeName/config/dbtables.php")){
            include_once ADDONS_ROOT . "$codeName/config/dbtables.php";
        }

        if (is_file(ADDONS_ROOT . "$codeName/config/bbcodes.php")){
            $bbcodes = include_once ADDONS_ROOT . "$codeName/config/bbcodes.php";

            foreach ($bbcodes as $bbcode){
                DataKeeper::InsertTo("tt_plugin_bbcode", ["ofPlugin" => self::GetPluginId($codeName),
                    "is_posix" => $bbcode["is_posix"],
                    "is_function" => $bbcode["is_function"],
                    "pattern" => $bbcode["pattern"],
                    "replacement" => $bbcode["replacement"]]);
            }
        }

        if (is_file(ADDONS_ROOT . "$codeName/config/navbar.php")){
            include_once ADDONS_ROOT . "$codeName/config/navbar.php";

            foreach ($navButtons as $button){
                $parent = DataKeeper::Get("tt_plugin_navbar", ["parent"],
                    ["content" => $button["parent"] ?? ""])[0]["parent"] ?? 0;

                echo
                    "\"" . self::AddNavbarBtn($lastId, $button["type"], $button["content"], $parent, $button["action"]) . "\"";
            }
        }
        return false;
    }

    public static function GetInstalledPlugins(){
        self::$installed = [];

        $queryResponse = DataKeeper::Get("tt_plugins", ["id", "name", "codename", "description", "status"]);
        foreach ($queryResponse as $response){
            self::$installed[$response["codename"]] = [
                "id" => $response["id"],
                "name" => $response["name"],
                "codeName" => $response["codename"],
                "description" => $response["description"],
                "status" => $response["status"]];

        }
        return self::$installed;
    }

    public static function DeletePlugin(string $codeName){
        $id = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codeName]);
        DataKeeper::Delete("tt_plugins", ["id" => $id[0]["id"]]);
        DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $id[0]["id"]]);
        DataKeeper::Delete("tt_plugin_permissions", ["ofPlugin" => $id[0]["id"]]);
        DataKeeper::Delete("tt_plugin_bbcode", ["ofPlugin" => $id[0]["id"]]);
        DataKeeper::Delete("tt_plugin_navbar", ["ofPlugin" => $id[0]["id"]]);
    }

    public static function Integration(string $main){
        $mysqli = new \mysqli(Engine::GetDBInfo(0), Engine::GetDBInfo(1), Engine::GetDBInfo(2), Engine::GetDBInfo(3));

        if ($mysqli->errno) {
            throw new NotConnectedToDatabaseError("Cannot connect to database");
        }

        $notStrict = [];
        $Strict    = [];

        if ($queryResponse = DataKeeper::MakeQuery("SELECT `trace`.`ofPlugin`, 
			                                                      `trace`.`system_text`, 
                                                                  `trace`.`system_text_to`,
                                                                  `trace`.`strict`,  
                                                                  `plugins`.`codename`
                                                           FROM `tt_plugin_trace` AS `trace`
                                                           LEFT JOIN `tt_plugins` AS `plugins` ON `trace`.ofPlugin = `plugins`.`id`
                                                           ",[], true)) {
            foreach ($queryResponse as $item){
                if ($item["strict"] === 0)
                    array_push($notStrict, ["ofPlugin" => $item["ofPlugin"],
                        "system_text" => $item["system_text"],
                        "system_text_to" => $item["system_text_to"],
                        "codename" => $item["codename"]]);
                else {
                    array_push($Strict, ["ofPlugin" => $item["ofPlugin"],
                        "system_text" => $item["system_text"],
                        "system_text_to" => $item["system_text_to"],
                        "codename" => $item["codename"]]);
                }
            }
        }

        /** @var $notStrict array use str_replace instead of str_replace_once */
        $forPage = "";
        foreach($notStrict as $value){
            if (strstr($main, $value["system_text"]) !== false) {
                if (file_exists(ADDONS_ROOT . "{$value["codename"]}/bin/{$value["system_text_to"]}")) {
                    include_once ADDONS_ROOT . "{$value["codename"]}/bin/{$value["system_text_to"]}";
                    $forPage = getBrick();
                } else {
                    $forPage = "Тhis file does not exist.";
                    $plugId = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $value["codename"]]);
                    DataKeeper::Delete("tt_plugins", ["codename" => $value["codename"]]);
                    DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_permissions", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_bbcode", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_navbar", ["ofPlugin" => $plugId]);
                }

                $main = str_replace($value["system_text"], $forPage, $main);
            }
        }

        foreach($Strict as $value) {
            if (strstr($main, $value["system_text"]) !== false) {
                if (file_exists(ADDONS_ROOT . "{$value["codename"]}/bin/{$value["system_text_to"]}")) {
                    include_once ADDONS_ROOT . "{$value["codename"]}/bin/{$value["system_text_to"]}";
                    $forPage = getBrick();
                } else {
                    $forPage = "This file does not exist.";
                    $plugId = DataKeeper::Get("tt_plugins", ["id"], ["codename" => $value["codename"]]);
                    DataKeeper::Delete("tt_plugins", ["codename" => $value["codename"]]);
                    DataKeeper::Delete("tt_plugin_trace", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_permissions", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_bbcode", ["ofPlugin" => $plugId]);
                    DataKeeper::Delete("tt_plugin_navbar", ["ofPlugin" => $plugId]);
                }

                $main = str_replace_once($value["system_text"], $forPage, $main);
            }
        }

        return $main;
    }

    public static function IntegrateCSS(string $string){
        $css = array_unique(self::$cssLines);
        $css = implode("", $css);
        $string = str_replace_once("{PLUGINS_STYLESHEETS}", $css, $string);

        return $string;
    }

    public static function IntegrateFooterJS(string $string){
        $footerJS = array_unique(self::$jsFooterLines);
        $footerJS = implode("", $footerJS);
        $string = str_replace_once("{PLUGIN_FOOTER_JS}", $footerJS, $string);

        return $string;
    }

    public static function IntegrateHeaderJS(string $string) {
        $headerJS = array_unique(self::$jsHeadLines);
        $headerJS = implode("", $headerJS);
        $string = str_replace_once("{PLUGIN_HEAD_JS}", $headerJS, $string);

        return $string;
    }

    public static function GetPluginId(string $codename){
        return DataKeeper::Get("tt_plugins", ["id"], ["codename" => $codename])[0]["id"];
    }
    /**
     * Return translation in dependence of site language.
     * If file with site language doesn't exist then return English version.
     *
     * @param string $pluginName Plugin's name
     * @param string $translationLocate Translation path
     * @return array|mixed|string|null
     * @throws \Exception
     */
    public static function GetTranslation(string $varPath, ...$vars){
        $path       = explode(".", $varPath);
        $pluginName = reset($path);

        if (file_exists(ADDONS_ROOT . "$pluginName/languages/" . Engine::GetEngineInfo("sl") . ".php")){
            $languageFile = Engine::GetEngineInfo("sl");
        } elseif (file_exists(ADDONS_ROOT . "$pluginName/languages/English.php")){
            $languageFile = "English";
        } else {
            throw new PluginError("Language file for plugin does not find", 38);
        }

        if (!is_dir(ADDONS_ROOT . "$pluginName"))
            throw new PluginNotFoundError("Plugin with name \"$pluginName\" did not found", 39);
        include ADDONS_ROOT . "$pluginName/languages/$languageFile.php";
        $language = $languagePack;

        unset($path[0]);

        $think = null;
        for ($i = 1; $i <= count($path); $i++) {
            if (empty($think)) {
                //If $think is empty set it into var.
                $think = $language[$path[$i]];
            } else {
                if (is_array($think)) {
                    $think = $think[$path[$i]];
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

    /** Return value of permission with name.
     *
     * @param integer $pluginId Plugin ID.
     * @param string $permissionName Permission name.
     * @param int $groupId ID of group for checking.
     * @return bool
     */
    public static function GetPermissionValue(int $pluginId, string $permissionName, int $groupId) : bool {
        $value = DataKeeper::Get("tt_plugin_permissions", ["value"], ["ofPlugin" => $pluginId, "codename" => $permissionName, "ofGroup" => $groupId])[0]["value"];
        if (!empty($value)){
            if ($value <= 0)
                return false;
            else
                return true;
        } return false;
    }

    /** Set value of permission with name.
     *
     * @param int $ofPlugin ID parent plugin for permission.
     * @param string $permissionName Permission name.
     * @param int $groupId ID of group for checking.
     * @param bool $value New value of permission.
     * @return bool
     */
    public static function SetPermissionValue(int $ofPlugin, string $permissionName, int $groupId, int $value) : bool {
        return DataKeeper::Update("tt_plugin_permissions",
            ["value" => $value],
            ["codename" => $permissionName,
                "ofGroup" => $groupId,
                "ofPlugin" => $ofPlugin]);
    }

    public static function IsTurnOn($identificator) : bool {
        if (gettype($identificator) == "string") {
            $result = DataKeeper::Get("tt_plugins", ["status"], ["codename" => $identificator])[0];
        }
        if (gettype($identificator) == "integer"){
            $result = DataKeeper::Get("tt_plugins", ["status"], ["id" => $identificator])[0];
        }
        return (bool)$result["status"];
    }

    /** Return associative array with codename and value of permissions of group.
     * If $groupId is not null return permissions codename and value of that group.
     * If permissions doesn't exist return false.
     *
     * @param int $pluginId
     * @param int|null $groupId
     * @return array|bool
     */
    public static function GetPermissionsOfPlugin(int $pluginId, int $groupId = null) {
        if (is_null($groupId))
            $array = DataKeeper::Get("tt_plugin_permissions", ["codename", "translate_path", "value"], ["ofPlugin" => $pluginId]);
        else
            $array = DataKeeper::Get("tt_plugin_permissions", ["codename", "translate_path", "value"], ["ofPlugin" => $pluginId, "ofGroup" => $groupId]);

        if (!empty($array))
            return $array;
        else
            return false;
    }

    public static function ProcessingBBCodeFromDB(string &$string){
        $patterns = DataKeeper::MakeQuery("SELECT `bbcode`.* 
                                                     FROM `tt_plugin_bbcode` AS `bbcode`
                                                     LEFT JOIN `tt_plugins` AS `plugins` ON `bbcode`.`ofPlugin` = `plugins`.`id`
                                                     WHERE `plugins`.`status` = ?", [1], true);

        $plugins = PluginManager::GetInstalledPlugins();
        foreach ($plugins as $plugin) {
            if (!self::IsTurnOn($plugin["codeName"]))
                continue;
            if (file_exists(ADDONS_ROOT . "{$plugin["codeName"]}/config/functions.php"))
                include_once ADDONS_ROOT . "{$plugin["codeName"]}/config/functions.php";
        }
        foreach ($patterns as $pattern){
            if ($pattern["is_posix"] == 1) {
                if ($pattern["is_function"] == 1) {
                    $string = preg_replace_callback($pattern["pattern"], function ($id) use ($pattern){
                        if (function_exists($pattern["replacement"])){
                            return call_user_func($pattern["replacement"], $id);
                        } else {
                            echo 4;
                            throw new \BadFunctionCallException("Function \"" . $pattern["replacement"] . "\" does not exist.");
                        }
                    }, $string);
                } else {
                    $string = preg_replace($pattern["pattern"], $pattern["replacement"], $string);
                }
            } else {
                $string = str_replace($pattern["pattern"], $pattern["replacement"], $string);
            }
        }
    }
}