<?php


/**
 * Authorization script.
 * Authorization script work for four conditions:
 * 1. If the account is not active - redirect to activation page.
 * 2. If the account is banned - redirect to ban welcome page.
 * 3. If enter to the account have been successefuly redirect to profile page.
**/
require_once "../../engine/main.php";
\Engine\Engine::LoadEngine();

/**
 * Authorization does those tests:
 * 1. If email or nickname contains invalid characters (and if it's invalid anyway) will send errors #22 or #21, respectively.
 * 2. If authorization has been failed by MySQL query reasons will send #9 error.
 * 3. If account is not active will send #26 error.
 * 4. If data is invalid (ID or PWD) SessionCreate function will send #25 error.
 */

if (empty($_REQUEST["profile-auth-uid"])){
    header("Location: ../../profile.php?res=nsuid");
    exit;
}
if (empty($_REQUEST["profile-auth-password"])){
    header("Location: ../../profile.php?res=nspwd");
    exit;
}
$session = \Users\UserAgent::SessionCreate($_REQUEST["profile-auth-uid"], $_REQUEST["profile-auth-password"]);
echo $session;
if ($session === TRUE){
    header("Location: ../../profile.php?uid=" . $_SESSION["uid"]);
    exit;
} elseif ($session == 25) {
    header("Location: ../../profile.php?res=iad");
    exit;
} elseif (in_array($session, [21,22])){
    header("Location: ../../profile.php?res=iuid");
    exit;
} elseif ($session == 9){
    header("Location: ../../profile.php?res=dbe");
    exit;
} elseif ($session == 26){
    header("Location: ../../profile.php?activate");
    exit;
}




