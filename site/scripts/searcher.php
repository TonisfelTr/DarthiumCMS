<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

if (isset($_POST["search-start-btn"])){
    $lookingFor = $_POST["search-input"];
    $searchType = $_POST["search-param"];
    header("Location: /index.php?search=$lookingFor&param=$searchType");
    exit;
}
