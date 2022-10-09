<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";

if (empty($_POST["text"])) exit;

echo \Engine\Engine::CompileBBCode($_POST["text"]);