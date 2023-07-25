<?php

use Builder\Controllers\BuildManager;
use Builder\Controllers\TagAgent;
use Builder\Controllers\BannerAgent;
use Engine\Engine;
use Engine\RouteAgent;
use Forum\ForumAgent;
use Forum\Models\Topic;
use Forum\StaticPagesAgent;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\Services\Session;
use Users\UserAgent;

/*****************************************************************************
 * TONISFEL TAVERN CMS.
 *
 * Author: Bagdanov Ilya.
 *
 * This page works by output buffering. Every part of template builds
 * high-grade system that we see for the request index.php.
 *
 *  Parts have code names that should replaced by HTML elements in html files
 *  in /site/templates/<template_name> directory.
 * */


define("CURRENT_MODULE", "main");

$main = BuildManager::includeFromTemplateAndCompile("main.html");

echo $main;

exit(1);
?>