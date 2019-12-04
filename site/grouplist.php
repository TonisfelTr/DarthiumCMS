<?php
if (!defined("TT_Index")){ header("index.php?page=errors/forbidden"); exit; }
if (\Users\GroupAgent::IsGroupExists($_GET["group"])) {
    $group = new \Users\Group($_GET["group"]);
} else {
    include_once "./site/errors/notfound.php";
    $groupPage = getBrick();
    echo $groupPage;
    exit;
}

$pageName = "Просмотр группы \"" . $group->getName() . "\"";

include_once "./site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/grouptable.html";
$groupPage = getBrick();
$groupPage = str_replace_once("{GROUP_COLOR}", $group->getColor(), $groupPage);
$groupPage = str_replace_once("{GROUP_NAME}", $group->getName(), $groupPage);
$groupPage = str_replace_once("{GROUP_DESCRIPTION}", $group->getDescript(), $groupPage);
$usersInGroup = \Users\GroupAgent::GetGroupUsers($group->getId(), (isset($_GET["p"])) ? $_GET["p"] : 1);
$tableUnit = "";
for ($i = 0; $i < count($usersInGroup); $i++){
    $number = $i +1;
    $tableUnit .= "<tr>
                        <td>$number</td>
                        <td><a href=\"profile.php?uid=$usersInGroup[$i]\">" . \Users\UserAgent::GetUserNick($usersInGroup[$i]) . "</a></td>
                   <tr>";
}

$groupPage = str_replace_once("{GROUP_USER_LIST}", $tableUnit, $groupPage);

$pageCount = \Users\GroupAgent::GetUsersCountInGroup($group->getId()) % 15;
if ($pageCount != 1){
    $groupTablePagination = "<div class=\"btn-group\">";
    for ($i = 0; $i < $pageCount; $i++)
        $groupTablePagination .= "<a class=\"btn btn-default\" href=?group=" . $group->getId() . "&p=$i>$i</a>";
    $groupTablePagination .= "</div>";
    $groupPage = str_replace_once("{GROUP_TABLE_PAGINATION}", $groupTablePagination, $groupPage);
} else
    $groupPage = str_replace_once("{GROUP_TABLE_PAGINATION}", "", $groupPage);

echo $groupPage;