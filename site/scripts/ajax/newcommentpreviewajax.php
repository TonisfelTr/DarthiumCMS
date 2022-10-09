<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";

$text = $_POST["text"];

$text = \Engine\Engine::CompileBBCode($text);

echo $text;
exit;