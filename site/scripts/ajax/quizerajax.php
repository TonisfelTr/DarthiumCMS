<?php
include "../../../engine/main.php";
\Engine\Engine::LoadEngine();

@$userId = $_POST["user_id"];
@$quizeId = $_POST["quize_id"];
@$varId = $_POST["var_id"];

if (\Users\UserAgent::IsUserExist($userId)){
    \Forum\ForumAgent::VoteInQuize($userId, $quizeId, $varId);
    echo "ok";
}
else
    echo "bad";