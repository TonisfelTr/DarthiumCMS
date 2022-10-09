<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 05.02.2018
 * Time: 2:35
 */

// Errors:
// rins - receiver is not set.
// rine - receiver is not exist.
// nt - no text
// mhnbs - message has not been sended.
// nprm - not permitted to read message.
// nprmm - not permitted to remove message or error of mysql. exception is the first part.
// nmid - no message id.
// yibl - you are in blacklist.
// Successes:
// mhbs - message has been sended.
// mhbr - message has been removed.

include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

$session = \Users\UserAgent::SessionContinue();
if ($session === false){
    header("Location: ../../profile.php?res=nsi");
    exit;
}

$user = new \Users\Models\User($_SESSION["uid"]);

//Send message.
if (isset($_REQUEST["send"])){
    if (empty($_POST["profile-pm-receiver-input"])){
        header("Location: ../../profile.php?page=wm&res=rins");
        exit;
    }

    if (!\Users\UserAgent::IsNicknameExists($_POST["profile-pm-receiver-input"])){
        header("Location: ../../profile.php?page=wm&res=rine");
        exit;
    }

    if (empty($_POST["profile-pm-text"])){
        header("Location: ../../profile.php?page=wm&res=nt");
        exit;
    }

    $bl = new \Users\UserBlacklister(\Users\UserAgent::GetUserId($_POST["profile-pm-receiver-input"]));

    if ($bl->isBlocked($user->getId())){
        header("Location: ../../profile.php?page=wm&res=yibl");
        exit;
    }

    if ($user->MessageManager()->send(\Users\UserAgent::GetUserId($_POST["profile-pm-receiver-input"]),
        (!empty($_POST["profile-pm-subject-input"])) ? $_POST["profile-pm-subject-input"] : "Без темы",
         $_POST["profile-pm-text"])){
        header("Location: ../../profile.php?page=ic&res=mhbs");
        exit;
    } else {
        header("Location: ../../profile.php?page=wm&res=mhnbs");
        exit;
    }
}

//Read message.
if (!empty($_REQUEST["r"])) {
    if ($user->MessageManager()->read($_REQUEST["r"]) !== false) {
        header("Location: ../../profile.php?page=rm&mid=" . $_REQUEST["r"]);
        exit;
    } else {
        header("Location: ../../profile.php?page=pm&res=nprm");
        exit;
    }
}

//Quote message.
if (!empty($_REQUEST["q"])){
    $letter = $user->MessageManager()->read($_REQUEST["q"]);
    if ($letter !== false){
        if (!\Users\UserAgent::IsNicknameExists($letter["senderUID"])){
            header("Location: ../../profile.php?page=wm&res=rine");
            exit;
        }
        header("Location: ../../profile.php?page=wm&sendTo=" . \Users\UserAgent::GetUserNick($letter["senderUID"]) . "&mid=" . $_REQUEST["q"]);
        exit;
    }
    else {
        header("Location: ../../profile.php?page=pm&res=nprm");
        exit;
    }
}

//Delete message.
if (!empty($_REQUEST["d"])){
    if ($user->MessageManager()->remove($_REQUEST["d"]) === true){
        header("Location: ../../profile.php?page=pm&res=mhbr");
        exit;
    } else {
        header("Location: ../../profile.php?page=pm&res=nprmm");
        exit;
    }
}

if (!empty($_REQUEST["rt"])){
    $result = $user->MessageManager()->restore($_REQUEST["rt"]);
    if ($result) {
        header("Location: ../../profile.php?page=pm&res=mhbrt");
        exit;
    }
}

header("Location: ../../profile.php?page=pm");
exit;