<?php
/** Response list:
 *  1. nsnfb - not sended nickname for blacklisting.
 *  2. une - user not exists.
 *  3. uiab - user is already blacklisted.
 *  4. uhbb - user has been blocked;
 *  5. uhnbb - user has not been blocked;
 *
 *  For deleting:
 *  6. uinb - user is not blocked;
 *  7. uhbub - user has been unblocked;
 *  8. cnuu - cannot unblock user;
 */
include_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

$session = \Users\UserAgent::SessionContinue();
if ($session !== TRUE){
    header("Location: ../../profile.php?res=nsi");
    exit;
}

$user = new \Users\User(@$_SESSION["uid"]);

if (isset($_REQUEST["profile-blacklist-add"])){
    if (empty($_REQUEST["profile-edit-blacklist-nickname"])){
        //Not sended nickname
        header("Location: ../../profile.php?page=blacklist&res=nsnfb");
        exit;
    }
    if (!\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"])){
        //User is not exitst.
        header("Location: ../../profile.php?page=blacklist&res=une");
        exit;
    }
    if ($user->Blacklister()->isBlocked(\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"]))){
        //User is already blocked.
        header("Location: ../../profile.php?page=blacklist&res=uiab");
        exit;
    }
    if ($user->Blacklister()->add(\Users\UserAgent::GetUserId($_REQUEST["profile-edit-blacklist-nickname"]), @$_REQUEST["profile-edit-blacklist-comment"])){
        //User has been blocked.
        header("Location: ../../profile.php?page=blacklist&res=uhbb");
        exit;
    } else {
        header("Location: ../../profile.php?page=blacklist&res=uhnbb");
        exit;
    }

}

if (isset($_REQUEST["buid"])){
    if (!$user->Blacklister()->isBlocked($_REQUEST["buid"])){
        //User is not blocked;
        header("Location: ../../profile.php?page=blacklist&res=uinb");
        exit;
    }
    if ($user->Blacklister()->remove($_REQUEST["buid"])){
        //User has been unblocked;
        header("Location: ../../profile.php?page=blacklist&res=uhbub");
        exit;
    } else {
        //Cannot unblocked user;
        header("Location: ../../profile.php?page=blacklist&res=cnuu");
        exit;
    }
}