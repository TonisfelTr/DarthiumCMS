<?php
require_once "../../../engine/engine.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\Models\User($_SESSION["uid"]);
else { header("Location: ../../../adminpanel.php?p=forbidden"); exit; }

if (!$user->getUserGroup()->getPermission("change_engine_settings")){
    header("Location: ../../../adminpanel.php?res=1");
    exit;
}

if (isset($_POST["installPlugin"])){
    @$pathToPage = $_POST["pluginCodeName"];
    if (file_exists("../../../addons/$pathToPage/config/config.php")) {
        $installedPackageArray = include("../../../addons/$pathToPage/config/config.php");
        \Engine\PluginManager::InstallPlugin($installedPackageArray["name"], $installedPackageArray["codeName"], $installedPackageArray["description"], 0);
    }
}

if (isset($_POST["deletePlugin"])){
    @$codeName = $_POST["pluginCodeName"];
    \Engine\PluginManager::DeletePlugin($codeName);
}

if (isset($_POST["descriptionPlugin"])){
    @$pathToPage = $_POST["pluginCodeName"];
    if (file_exists("../../../addons/$pathToPage/config/config.php")) {
        $conf = include("../../../addons/$pathToPage/config/config.php");
        if (!isset($conf["description"]))
            $conf["description"] = "Just a new plugin!";
        echo $conf["description"];
    }
}

if (isset($_POST["turnModePlugin"])){
    @$mode = $_POST["mode"];
    @$codeName = $_POST["codename"];
    if (\Engine\DataKeeper::existsWithConditions("tt_plugins", ["codename" => $codeName]) == true)
        \Engine\DataKeeper::Update("tt_plugins", ["status" => $mode], ["codename" => $codeName]);
}

if (isset($_POST["getModePlugin"])){
    @$codeName = $_POST["codename"];
    if (\Engine\DataKeeper::existsWithConditions("tt_plugins", ["codename" => $codeName]) == true)
        echo \Engine\DataKeeper::Get("tt_plugins", ["status"], ["codename" => $codeName])[0]["status"];
}