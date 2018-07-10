<?php

include_once "../../../engine/main.php";

if (empty($_POST["text"])) exit;

echo \Engine\Engine::CompileBBCode($_POST["text"]);