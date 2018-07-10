<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 20.05.2018
 * Time: 23:38
 */

/* Negative:
 * 7nsn - [not] [sended] [name] of page.
 * 7nbn - [name] is[n't] [big]
 * 7nst - [not] [sended] [text] of page.
 * 7nbt - [text] is[n't] [big].
 * 7ntbd - [discription] is [too] [big] ([negative])
 * 7ncp - page has [not] been [created].
 * Positive:
 * 7scp - successfull create page.
*/
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()) $user = new \Users\User($_SESSION["uid"]);
else { header("Location: ../../adminpanel.php?p=forbidden"); exit; }

if (isset($_POST["staticc-page-create-create-btn"])){
    if ($user->UserGroup()->getPermission("sc_create_pages")){
        if (empty($_POST["staticc-page-create-name-input"])){
            header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nsn");
            exit;
        }
        if (strlen($_POST["staticc-page-create-name-input"]) < 4){
            header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nbn");
            exit;
        }

        if (empty($_POST["staticc-page-create-textarea"])){
            header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nst");
            exit;
        }
        if (strlen($_POST["staticc-page-create-textarea"]) < 20){
            header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7nbt");
            exit;
        }
        if (!empty($_POST["staticc-page-create-description-input"]) && strlen($_POST["staticc-page-create-description-input"]) > 100){
            header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7ntbd");
            exit;
        }

        if ($e = \Forum\StaticPagesAgent::CreatePage($_POST["staticc-page-create-name-input"], $user->getId(),
            (!empty($_POST["staticc-page-create-description-input"])) ? $_POST["staticc-page-create-description-input"] : "", $_POST["staticc-page-create-textarea"])){
            header("Location: ../../adminpanel.php?p=staticc&res=7scp");
            exit;
        } else {
            //header("Location: ../../adminpanel.php?p=staticc&reqtype=1&res=7ncp");
            exit;
        }

    }
} else {
    header("Location: ../../adminpanel.php?p=staticc&res=1");
    exit;
}

header("Location: ../../adminpanel.php?p=staticc");