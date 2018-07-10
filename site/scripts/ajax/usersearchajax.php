<?php

include "../../../engine/main.php";
\Engine\Engine::LoadEngine();

if (empty($_REQUEST["nickname"])){ exit;}

$array = \Users\UserAgent::GetUsersList(["nickname" => $_REQUEST["nickname"] . "*"], 1);
$index = -1;
if (count($array) > 5) $index = 4;
else $index = count($array)-1;

if ($index >= 0){
    for ($i = 0; $i <= $index; $i++) {
        $user = new \Users\User($array[$i]);
        $result[$i] = [$user->getAvatar(), $user->getNickname(), $user->UserGroup()->getName(), "\"" . $user->getId() . "\""];
    }
    echo @serialize($result);
} else {
    echo "Error!";
}
exit;