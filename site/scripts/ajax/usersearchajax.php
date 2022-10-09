<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;
\Engine\Engine::LoadEngine();

if (empty($_REQUEST["nickname"])){ exit;}

$array = \Users\UserAgent::GetUsersList(["nickname" => $_REQUEST["nickname"] . "*"], 1);
$index = -1;
if (count($array) > 5) $index = 4;
else $index = count($array)-1;

if ($index >= 0){
    foreach ($array as $item) {
        $user = new \Users\Models\User($item["id"]);
        $result[] = [$user->getAvatar(), $user->getNickname(), $user->UserGroup()->getName(), "\"" . $user->getId() . "\""];
    }
    echo @serialize($result);
} else {
    echo "Error!";
}

exit;