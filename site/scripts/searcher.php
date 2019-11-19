<?php

include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if (isset($_POST["search-start-btn"])){
    $lookingFor = $_POST["search-input"];
    header("Location: /index.php?search=$lookingFor");
    exit;
}
