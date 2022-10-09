<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";;
\Engine\Engine::LoadEngine();

@$userId = $_POST["user_id"];
@$quizeId = $_POST["quize_id"];
@$varId = $_POST["var_id"];

if (\Users\UserAgent::IsUserExist($userId)){
    if (!\Forum\ForumAgent::IsVoted($userId, $quizeId)) {
        \Forum\ForumAgent::VoteInQuize($userId, $quizeId, $varId);
        echo "ok";
    }
}
else
    echo "bad";