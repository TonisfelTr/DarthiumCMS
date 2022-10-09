<?php

include_once "../../engine/engine.php";
\Engine\Engine::LoadEngine();

if (\Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){
    header("Location: banned.php");
    exit;
}

$session = \Users\UserAgent::SessionContinue();
if ($session !== TRUE){
    header("Location: ../../profile.php?res=nsi");
    exit;
}

$user = new \Users\Models\User($_SESSION["uid"]);

if ($user->UserGroup()->getPermission("change_profile")) {

    if (!empty($_FILES["profile-edit-avatar"])){
        $res = \Users\UserAgent::UploadAvatar($user->getId(), "profile-edit-avatar");
        if ($res === False) {
            header("Location: ../../profile.php?page=edit&res=neav");
            exit;
        }
        elseif ($res === 18){
            header("Location: ../../profile.php?page=edit&res=neavvf");
            exit;
        }
        elseif ($res === 19) {
            header("Location: ../../profile.php?page=edit&res=neavvs");
            exit;
        }
        elseif ($res === 20) {
            header("Location: ../../profile.php?page=edit&res=neavvb");
            exit;
        }
        elseif ($res === true) {
            header("Location: ../../profile.php?res=seav");
            exit;
        }
        else {
            header("Location: ../../profile.php?page=edit&res=neav");
            exit;
        }
        exit;
    }

    \Users\UserAgent::ChangeUserParams($user->getId(), "city", @$_POST["profile-edit-city"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "vk", @$_POST["profile-edit-vk"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "skype", @$_POST["profile-edit-skype"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "realname", @$_POST["profile-edit-realname"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "birth", @$_POST["profile-edit-birth"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "sex", @$_POST["profile-edit-sex"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "about", @$_POST["profile-edit-about"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "hobbies", @$_POST["profile-edit-hobbies"]);
    \Users\UserAgent::ChangeUserParams($user->getId(), "signature", @$_POST["profile-edit-signature"]);
    if (isset($_POST["profile-public-vk"]))
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_vk", 1);
    else
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_vk", 0);
    if (isset($_POST["profile-public-skype"]))
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_skype", 1);
    else
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_skype", 0);
    if (isset($_POST["profile-public-email"]))
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_email", 1);
    else
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_email", 0);
    if (isset($_POST["profile-public-birth"]))
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_birthday", 1);
    else
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_birthday", 0);
    if (isset($_POST["profile-public-acc"]))
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_account", 1);
    else
        \Users\UserAgent::ChangeUserParams($user->getId(), "public_account", 0);

    $adFields = \Users\UserAgent::GetAdditionalFieldsList();
    foreach ($adFields as $field){
        \Users\UserAgent::SetAdditionalFieldContent($user->getId(), $field["id"], @$_POST["profile-edit-" . $field["id"]]);
        if (isset($_POST["profile-public-" . $field["id"]])){
            \Users\UserAgent::SetPrivacyToAdditionalField($user->getId(), $field["id"], true);
            echo 3;
        }
        else {
            \Users\UserAgent::SetPrivacyToAdditionalField($user->getId(), $field["id"], false);
            echo 2;
        }
    }

} elseif (empty($_POST["profile-change-pass-checkbox"])){
    header("Location: ../../profile.php?res=nsi");
    exit;
}

if (isset($_POST["profile-change-pass-checkbox"])) {
    if (empty($_POST["profile-change-oldpass"])){
        header("Location: ../../profile.php?page=security&res=nsop");
        exit;
    }
    if (empty($_POST["profile-change-newpass"])){
        header("Location: ../../profile.php?page=security&res=nsnp");
        exit;
    }
    if (empty($_POST["profile-change-renewpass"])){
        header("Location: ../../profile.php?page=security&res=nsrnp");
        exit;
    }
    if ($_SESSION["passhash"] == hash("sha256", $_POST["profile-change-oldpass"])){
        if ($_POST["profile-change-newpass"] == $_POST["profile-change-renewpass"]){
            $user->passChange($_POST["profile-change-newpass"], true);
            header("Location: ../../profile.php?page=security&res=phbc");
            exit;
        } else {
            header("Location: ../../profile.php?page=security&res=narnpane");
            exit;
        }
    } else {
        header("Location: ../../profile.php?page=security&res=iop");
        exit;
    }
}

if ($user->UserGroup()->getPermission("change_profile") && empty($_POST["profile-change-pass-checkbox"])) {
    header("Location: ../../profile.php?page=edit&res=aphbs");
    exit;
} elseif ($user->UserGroup()->getPermission("change_profile") && !empty($_POST["profile-change-pass-checkbox"])){
    header("Location: ../../profile.php?page=edit&res=apaphbs");
    exit;
}

header("Location: ../../profile.php");
exit;