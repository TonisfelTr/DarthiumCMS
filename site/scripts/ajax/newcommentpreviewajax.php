<?php
include_once "../../../engine/main.php";

$text = $_POST["text"];

$text = \Engine\Engine::CompileBBCode($text);

echo $text;
exit;