<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/1/16
 * Time: 5:50 AM
 */
include "engine/main.php";
define("TT_AP", true);
ob_start();
\Engine\Engine::LoadEngine();

if ($sessionRes = \Users\UserAgent::SessionContinue()){
    $user = new \Users\User($_SESSION["uid"]);
}
else {
    header("Location: profile.php");
    exit;
}
//Проверка на наличие доступа в АП.
if (!isset($user) || !$user->UserGroup()->getPermission("enterpanel")){ header("Location: index.php?page=errors/forbidden"); exit; }
if (isset($user)) if ($user->isBanned()) { header("Location: banned.php"); exit; }
if( \Guards\SocietyGuard::IsBanned($_SERVER["REMOTE_ADDR"], true)){ header("Location: banned.php"); exit; }

function getBrick(){
    $e = ob_get_contents();
    ob_clean();
    return $e;
}

function str_replace_once($search, $replace, $text){
    $pos = strpos($text, $search);
    return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

?>
<!DOCTYPE HTML>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo \Engine\LanguageManager::GetTranslation("header") . " - " . \Engine\Engine::GetEngineInfo("sn");?></title>
    <script src="libs/js/ie-emulator.js"></script>
    <script src="libs/js/jquery-3.1.0.min.js"></script>
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <link href="libs/bootstrap/css/ie10-viewport.css" rel="stylesheet">
    <link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="libs/bootstrap/css/glyphicons-regular.css" rel="stylesheet">
    <link rel="stylesheet" href="libs/codemirror/lib/codemirror.css">
    <link href="adminpanel/css/ap-style.css" rel="stylesheet">
    <link href="adminpanel/css/uploader-style.css" rel="stylesheet">
    <link href="adminpanel/css/icon.ico" rel="icon">
    <script src="libs/bootstrap/js/bootstrap.min.js"></script>
    <script src="libs/codemirror/lib/codemirror.js"></script>
    <script src="libs/codemirror/mode/javascript/javascript.js"></script>
    <script src="libs/codemirror/mode/xml/xml.js"></script>
    <script src="libs/codemirror/mode/css/css.js"></script>
    <script src="libs/codemirror/mode/php/php.js"></script>
    <script src="libs/codemirror/mode/htmlembedded/htmlembedded.js"></script>
    <script src="libs/codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="libs/codemirror/mode/clike/clike.js"></script>
    {PLUGINS_STYLESHEETS}
    <?php if (@$_GET["p"] == "staticc")
    echo "<link href=\"site/templates/" . \Engine\Engine::GetEngineInfo("stp") . "/css/sp-style.css\" rel=\"stylesheet\">";
    ?>
</head>
<body>
<?php include "adminpanel/subpanels/uploader.php"; ?>

<div class="wrapper">
    <div class="container">
        <!-- Static navbar -->
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"><?= \Engine\LanguageManager::GetTranslation("navigation"); ?></a>
                </div>
                <div class="navbar-collapse collapse" id="navbar">
                    <ul class="nav navbar-nav">
                        <li <?php if (!isset($_GET["p"]) && !isset($_GET["plp"])) echo "class='active'"; ?>><a href="adminpanel.php"><?php echo \Engine\LanguageManager::GetTranslation("home");?></a></li>
                        <li <?php if (isset($_GET["p"])) if ($_GET["p"] == 'settings') echo "class='active'"; ?>><a href="?p=settings"><?php echo \Engine\LanguageManager::GetTranslation("settings");?></a></li>
                        <li <?php if (isset($_GET["p"])) if ($_GET["p"] == 'reports') echo "class='active'"; ?>><a href="?p=reports"><?php echo \Engine\LanguageManager::GetTranslation("reports");?>
                                <?php if (($rc = \Guards\ReportAgent::GetUnreadedReportsCount()) > 0) { ?><span class="adminpanel-reports-inc"><span class="glyphicons glyphicons-bell"></span> <?php echo $rc; ?></span><?php } ?></a></li>
                        <li <?php if (isset($_GET["p"])) if ($_GET["p"] == 'logs') echo "class='active'"; ?>><a href="?p=logs"><?php echo \Engine\LanguageManager::GetTranslation("logs");?></a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="index.php"><?php echo \Engine\LanguageManager::GetTranslation("to_site_home");?></a></li>
                        <li><a href="profile.php"><?php echo $user->getNickname(); ?></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="jumbotron" id="jumbotron">
            <h1><?php echo \Engine\LanguageManager::GetTranslation("header");?></h1>
            <p><?php echo \Engine\LanguageManager::GetTranslation("header_info");?></p>
        </div>
    </div>
    <div class="divider_header">

    </div><br>
    <?php
    #####################################################################################
    /* Раздел с ошибками. Здесь находится div, внутри которого форма для вывода ошибок. */
    #####################################################################################
    if (isset($_GET["res"])){ ?> <div class="container-fluid"><?php
        if ($_GET["res"] == "1"){ ?><div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("not_permited");?></div><?php }
        if ($_GET["res"] == "2"){ ?><div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("no_page_in_module");?></div><?php }
        if (isset($_GET["p"])) {
            if ($_GET["p"] == "settings") {
                if ($_GET["res"] == "2s") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_save");?>
                    </div><?php }
                if ($_GET["res"] == "2n") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("settings_not_save");?>
                    </div><?php }
            }
            if ($_GET["p"] == "groups") {
                if ($_GET["res"] == "3se") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("custom_info_group"); ?>
                    </div><?php }
                if ($_GET["res"] == "3spc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("permissions_change_and_set_success"); ?>
                    </div><?php }
                if ($_GET["res"] == "3sgc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_create_success"); ?>
                    </div><?php }
                if ($_GET["res"] == "3sgd") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_remove_success"); ?>
                    </div><?php }
                if ($_GET["res"] == "3nlfs") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("invalid_group_name"); ?>
                    </div><?php }
                if ($_GET["res"] == "3nmfts") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("invalid_group_name"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ne") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("not_permited_change_custom_info_group"); ?>
                    </div><?php }
                if ($_GET["res"] == "3npc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("not_permited_change_perms_group"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ngc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_create_error"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ngd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("group_remove_error"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ndd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("special_group_remove_denied"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ngs") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("not_choice_group"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ngmm") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("failed_to_move_users_group"); ?>
                    </div><?php }
                if ($_GET["res"] == "3ngsd") { ?>
                    <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("not_permited_remove_custom_group"); ?>
                    </div><?php }
            }
            if ($_GET["p"] == "users"){
                if ($_GET["res"] == "4ncdu") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("cannot_remove_yourself_or_main_admin"); ?>
                    </div><?php }
                if ($_GET["res"] == "4sdu") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("users_have_been_successfully_deleted"); ?>
                    </div><?php }
                if ($_GET["res"] == "4ndu") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("users_have_not_been_deleted"); ?>
                    </div><?php }
                if ($_GET["res"] == "4ndus") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("users_were_not_deleted:_no_users_were_received_for_deletion"); ?>
                    </div><?php }
                if ($_REQUEST["res"] == "4nbu") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_to_block_this_user"); ?>
                    </div> <?php }
                if ($_REQUEST["res"] == "4sbu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_user_has_been_successfully_blocked"); ?>
                    </div> <?php }
                if ($_REQUEST["res"] == "4sbus") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("these_users_have_been_successfully_blocked"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nbus") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_block_users_by_template"); ?> "<?php echo htmlentities($_REQUEST["bnns"]); ?>".
                </div> <?php }
                if ($_REQUEST["res"] == "4nuu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_unlock_this_account");?>
                </div> <?php }
                if ($_REQUEST["res"] == "4suu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_account_have_been_successfully_unblocked"); ?>
                    </div> <?php }
                if ($_REQUEST["res"] == "4nbeu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_account_does_not_exist"); ?>
                    </div> <?php }
                if ($_REQUEST["res"] == "4nibu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_account_is_already_blocked"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nihs") { ?><div class="alert alert-info"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("you_did_not_fill_in_the_IP_address_string"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4sib") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("the_IP_address_was_successfully_blocked"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nib") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_block_IP_address"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4niab") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                   <?php echo \Engine\LanguageManager::GetTranslation("this_IP_address_is_already_blocked"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nibe") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("internal_execution_error_the_request_to_the_database_failed");?>
                </div> <?php }
                if ($_REQUEST["res"] == "4niub") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_unlock_IP_address");?>
                </div> <?php }
                if ($_REQUEST["res"] == "4siub") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_IP_address_has_been_unblocked"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nrnn") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("nickname_cannot_be_empty"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nre") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("user_must_have_email"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nrp") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("user_must_have_password"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nru") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_register_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4sru") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("user"); ?> "<?php echo $_REQUEST["nunn"]; ?>" <?=\Engine\LanguageManager::GetTranslation("has_been_registered"); ?>
                </div> <?php }
                if (($_REQUEST["res"] == "4nvnn") || ($_REQUEST["res"] == "4nenvn")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("nickname_has_forbidden_symbols"); ?>
                    </div> <?php }
                if (($_REQUEST["res"] == "4nve") || ($_REQUEST["res"] == "4neve")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("you_entered_an_invalid_email_address"); ?>
                    </div> <?php }
                if (($_REQUEST["res"] == "4nnee") || ($_REQUEST["res"] == "4neee")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("a_user_with_such_nickname_or_email_is_already_there"); ?>
                    </div> <?php }
                if (($_REQUEST["res"] == "4nne") || ($_REQUEST["res"] == "4neenn")) { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("user_already_has_such_a_nickname");?>
                    </div> <?php }
                if ($_REQUEST["res"] == "4ncsafc") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_contents_of_additional_fields"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nep") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_password_for_this_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nef") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_from_column"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nev") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_vk_user_id"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nes") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_skype_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nesx") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_gender_field_of_the_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nern") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to change_the_real name_column_of_the_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nebd") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_column_date_of_birth_of_the_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nehs") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_user_hobby_list"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nea") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                   <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_the_column_about_me_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nesg") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_user_signature"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neav") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_change_user_avatar"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvf") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_file_cannot_be_an_avatar"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvs") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("avatar_has_the_wrong_size_make_sure_it_meets_the_requirements"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neavvb") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("an_avatar_weighs_more_than_6megabytes"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4seu") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("changes_have_been_saved"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4sua") { ?><div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("user_account"); ?> "<?php echo \Users\UserAgent::GetUserNick($_REQUEST["uid"]); ?>"
                    <?php echo \Engine\LanguageManager::GetTranslation("has_been_activated"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4nua") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_activate_this_user"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neu") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_user_does_not_exist"); ?>
                </div> <?php }
                if ($_REQUEST["res"] == "4neae") { ?><div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("such_email_is_already_registered"); ?>
                </div> <?php }
            }
            if ($_GET["p"] == "reports"){
                if ($_GET["res"] == "5nrid") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_id_report_for_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nnas") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_id_report_answer_for_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ncr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_answer_as_solve"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nmt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_answer_text"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ntsm") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("too_small_answer_text"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nad") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_public_answer"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ntr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("report_does_not_exist_to_answer_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nta") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("answer_does_not_exist_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ncds") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("denied_remove_answer_as_solve"); ?>
                    </div><?php }
                if ($_GET["res"] == "5sda") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("answer_has_been_removed_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nda") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_to_remove_answer"); ?>
                    </div><?php }
                if ($_GET["res"] == "5naacr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("denied_to_do_this_action_with_closed_report"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nroai") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_id_answer_or_report_for_edit"); ?>
                    </div><?php }
                if ($_GET["res"] == "5sad") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("your_answer_has_been_published"); ?>
                    </div><?php }
                if ($_GET["res"] == "5scr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("this_answer_marked_as_solve_report_has_been_closed"); ?>
                    </div><?php }
                if ($_GET["res"] == "5sea") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("answer_has_been_edited_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ser") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("report_text_has_been_edited_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "5sdr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("report_has_been_removed"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ndr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_report_removing"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nea") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_answer_edit"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ner") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_report_edit_text"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ne") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_report_with_this_id"); ?>
                    </div><?php }
                if ($_GET["res"] == "5nsrd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_selected_reports_to_remove"); ?>
                    </div><?php }
                if ($_GET["res"] == "5ndsr") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_to_remove_some_report"); ?>
                    </div><?php }
                if ($_GET["res"] == "5sdsr") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-info-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("selected_reports_have_been_removed"); ?>
                    </div><?php }
            }
            if ($_GET["p"] == "categories") {
                if ($_GET["res"] == "6ncid") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_category_id_for_this_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "6nct") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_category_with_that_id"); ?>
                    </div><?php }
                if ($_GET["res"] == "6ncc") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("failed_create_category"); ?>
                    </div><?php }
                if ($_GET["res"] == "6nct") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_category_with_that_id"); ?>
                    </div><?php }
                if ($_GET["res"] == "6scc") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("category_has_been_created_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "6nvcn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("category_wrong_name"); ?>
                    </div><?php }
                if ($_GET["res"] == "6ncn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_category_name"); ?>
                    </div><?php }
                if ($_GET["res"] == "6ncd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("not_setted_category_description"); ?>
                    </div><?php }
                if ($_GET["res"] == "6sce") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("category_has_been_edited_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "6scdt") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("category_has_been_removed_successfuly"); ?>
                    </div><?php }
                if ($_GET["res"] == "6ncdt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("cannot_remove_category"); ?>
                    </div><?php }

            }
            if ($_GET["p"] == "staticc") {
                if ($_GET["res"] == "7nsn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                        <?php echo \Engine\LanguageManager::GetTranslation("no_page_name"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nbn") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> <?php echo \Engine\LanguageManager::GetTranslation("page_name_too_small"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nst") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("no_id_for_this_action"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nbt") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("text_too_short"); ?>
                    </div><?php }
                if ($_GET["res"] == "7ntbd") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("description_is_too_long"); ?>
                    </div><?php }
                if ($_GET["res"] == "7ncp") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_create_static_page"); ?>
                    </div><?php }
                if ($_GET["res"] == "7npe") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("this_page_does_not_exist"); ?>
                    </div><?php }
                if ($_GET["res"] == "7ndsp") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_remove_some_pages"); ?>
                    </div><?php }
                if ($_GET["res"] == "7npse") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("failed_to_save_page_settings"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nspe") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("some_page_does_not_exist"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nssan") { ?>
                    <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("author_nickname_does_not_setted"); ?>
                    </div><?php }
                if ($_GET["res"] == "7nssn") { ?>
                    <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("static_page_name_is_not_setted"); ?>
                    </div><?php }
                if ($_GET["res"] == "7scp") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("static_page_has_been_created"); ?>
                    </div><?php }
                if ($_GET["res"] == "7sphbe") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("static_page_has_been_edited"); ?>
                    </div><?php }
                if ($_GET["res"] == "7srsp") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("selected_static_page_have_been_removed"); ?>
                    </div><?php }
            }
            if ($_GET["p"] == "emailsender" || $_GET["p"] == "pmsender"){
                if ($_GET["res"] == "8ses") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("mails_have_been_sended"); ?>
                    </div><?php }
            }
            if ($_GET["p"] == "uploadedlist"){
                if ($_GET["res"] == "9ndfs") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-delete"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("not_setted_files_for_deleted_error"); ?>
                    </div><?php }
                if ($_GET["res"] == "9ssfd") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("files_deleted_success"); ?>
                    </div><?php }
                if ($_GET["res"] == "9nsfi") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-trash"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("not_setted_file_id"); ?>
                    </div><?php }
                if ($_GET["res"] == "9sfd") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon"></span>
                    <?php echo \Engine\LanguageManager::GetTranslation("file_delete_success"); ?>
                    </div><?php }
            }
            if ($_GET["p"] == "teditor"){
                if ($_GET["res"] == "10scf") { ?>
                    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> <?php echo \Engine\LanguageManager::GetTranslation("file_has_been_edited_success"); ?>
                    </div><?php }
                if ($_GET["res"] == "10fcf") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("file_has_not_been_edited"); ?>
                    </div><?php }
                if ($_GET["res"] == "10fne") { ?>
                    <div class="alert alert-danger"><span class="glyphicon glyphicon-alert"></span> <?php echo \Engine\LanguageManager::GetTranslation("file_doesnot_exist"); ?>
                    </div><?php }
            }
        }
    ?></div><?php }
    ################################################
    /* Проверка на наличие раздела у админ панели */
    ################################################
    if (!isset($_GET["p"]) && !isset($_GET["plp"])) { ?>
    <div class="container-fluid">
        <div class="center"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.settings_site_and_engine"); ?></div>
        <hr/>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("change_engine_settings")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=settings"><span
                                class="glyphicon glyphicon-cog"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.settings"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.settings_description"); ?></p>
                </div>
            <?php } ?>
        </div>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("logs_see")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=logs"><span
                                class="glyphicon glyphicon-transfer"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.logs"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.logs_description"); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
    <br/>
    <div class="container-fluid">
        <div class="center"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.users_managment"); ?></div>
        <hr/>
        <?php if ($user->UserGroup()->getPermission("user_add") ||
            $user->UserGroup()->getPermission("user_remove") ||
            $user->UserGroup()->getPermission("user_ban") ||
            $user->UserGroup()->getPermission("user_unban") ||
            $user->UserGroup()->getPermission("user_banip") ||
            $user->UserGroup()->getPermission("user_unbanip")) { ?>
            <div class="col-lg-6">
                <div class="linker">
                    <a class="linkin" href="?p=users"><span
                                class="glyphicon glyphicon-user"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.users"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.users_description"); ?></p>
                </div>
            </div>
        <?php } ?>
        <?php if ($user->UserGroup()->getPermission("group_change") ||
            $user->UserGroup()->getPermission("group_create") ||
            $user->UserGroup()->getPermission("group_delete") ||
            $user->UserGroup()->getPermission("change_perms")) { ?>
            <div class="col-lg-6">
                <div class="linker">
                    <a class="linkin" href="?p=groups"><span
                                class="glyphicons glyphicons-group"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.groups"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.groups_description"); ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
    <br/>
    <div class="container-fluid">
        <div class="center"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.content_managment"); ?></div>
        <hr/>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("rules_edit")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=rules"><span
                                class="glyphicons glyphicons-list"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.rules"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.rules_description"); ?></p>
                </div> <?php }
            if ($user->UserGroup()->getPermission("category_create") ||
                $user->UserGroup()->getPermission("category_edit") ||
                $user->UserGroup()->getPermission("category_delete")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=categories"><span
                                class="glyphicons glyphicons-show-thumbnails"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.categories"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.categories_description"); ?></p>
                </div>
            <?php } ?>
            <?php if ($user->UserGroup()->getPermission("upload_see_all")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=uploadedlist"><span
                                class="glyphicons glyphicons-file-cloud-upload"></span> <?= \Engine\LanguageManager::GetTranslation("adminpanel.uploader_list") ?>
                    </a>
                    <p class="helper"><?= \Engine\LanguageManager::GetTranslation("adminpanel.uploader_list_description") ?></p>
                </div>
            <?php } ?>
        </div>
        <div class="col-lg-6">
            <?php if ($user->UserGroup()->getPermission("sc_create_pages") ||
                $user->UserGroup()->getPermission("sc_edit_pages") ||
                $user->UserGroup()->getPermission("sc_remove_pages") ||
                $user->UserGroup()->getPermission("sc_design_edit")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=staticc"><span
                                class="glyphicons glyphicons-pen"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.static_content_managment"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.static_content_managment_description"); ?></p>
                </div>
            <?php }
            if ($user->UserGroup()->getPermission("report_talking") &&
                $user->UserGroup()->getPermission("report_foreign_remove") &&
                $user->UserGroup()->getPermission("report_foreign_edit") &&
                $user->UserGroup()->getPermission("report_close")
            ) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=reports"><span
                                class="glyphicon glyphicon-fire"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.reports"); ?>
                    </a>
                    <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.reports_description"); ?></p>
                </div> <?php } ?>
            <?php if ($user->UserGroup()->getPermission("change_template_design")) { ?>
                <div class="linker">
                    <a class="linkin" href="?p=teditor"><span
                                class="glyphicons glyphicons-brush"></span> <?= \Engine\LanguageManager::GetTranslation("site_design.panel_name") ?>
                    </a>
                    <p class="helper"><?= \Engine\LanguageManager::GetTranslation("site_design.description") ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
    <br/>
    <?php if ($user->UserGroup()->getPermission("bmail_sende") ||
        $user->UserGroup()->getPermission("bmail_sends")) { ?>
        <div class="container-fluid">
            <div class="center"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.mail_sending"); ?></div>
            <hr>
            <div class="col-lg-6">
                <?php if ($user->UserGroup()->getPermission("bmail_sende")) { ?>
                    <div class="linker">
                        <a class="linkin" href="?p=emailsender"><span
                                    class="glyphicons glyphicons-file"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.email_sender"); ?>
                        </a>
                        <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.email_sender_description"); ?></p>
                    </div>
                <?php } ?>
            </div>
            <div class="col-lg-6">
                <?php if ($user->UserGroup()->getPermission("bmail_sends")) { ?>
                    <div class="linker">
                        <a class="linkin" href="?p=pmsender"><span
                                    class="glyphicons glyphicons-file-cloud"></span> <?php echo \Engine\LanguageManager::GetTranslation("adminpanel.pm_sender"); ?>
                        </a>
                        <p class="helper"><?php echo \Engine\LanguageManager::GetTranslation("adminpanel.pm_sender_description"); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div><br/>
    <?php } ?>
    <?php if ($user->UserGroup()->getPermission("plugins_control")) {
        $installedPluginsCount = count(\Engine\PluginManager::GetInstalledPlugins()) > 0;
        if ($installedPluginsCount){
            $installedPlugins = \Engine\PluginManager::GetInstalledPlugins();
            //Проверка на возможность редактирования плагина.
            if (true) { ?>
                <div class="container-fluid">
                    <div class="center"><?= \Engine\LanguageManager::GetTranslation("adminpanel.plugins_control") ?></div>
                    <hr>
                    <?php
                    foreach ($installedPlugins as $plugin) { ?>
                        <div class="col-lg-6">
                            <div class="linker">
                                <a class="linkin" href="?plp=<?= $plugin["codeName"] ?>"><span
                                            class="glyphicons glyphicons-log-in"></span> <?= $plugin["name"] ?></a>
                                <p class="helper"><?= (!\Engine\PluginManager::IsTurnOn($plugin["codeName"]) ?
                                        ("[" . \Engine\LanguageManager::GetTranslation("off") . "] ") : "") .
                                \Engine\PluginManager::GetTranslation($plugin["codeName"] . ".plugin_description") ?></p>
                            </div>
                        </div>
                    <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </div>
</div>
<?php
}   elseif (isset($_GET["p"]) && isset($_GET["plp"])) include_once "adminpanel/errors/invalidrequest.php";
    elseif (isset($_GET["p"])) { ?>
    <div class="container-fluid">
       <?php if (file_exists("adminpanel/".$_GET["p"].".php")) include_once "adminpanel/".$_GET["p"].".php";
             elseif ($_GET["p"] == "forbidden") include_once "adminpanel/errors/forbidden.php";
             else include_once "adminpanel/errors/notfound.php";?>
    </div>
    <?php } elseif (isset($_GET["plp"])) { ?>
        <div class="container-fluid">
        <?php
        if (file_exists("addons/".$_GET["plp"]."/bin/adminpanel.php")){
            ob_start();
            include_once "addons/".$_GET["plp"]."/bin/adminpanel.php";
            $plp = getBrick();
            ob_end_flush();
            echo \Engine\Engine::StripScriptTags($plp);
//            $plp = getBrick();
//            echo \Engine\Engine::StripScriptTags($plp);
        }
        else include_once "adminpanel/errors/notfound.php";?>
        </div>
    <?php } else include_once "adminpanel/errors/notfound.php"; ?>

<div class="footer">
    <p class="footer">
        Tonisfel Tavern CMS.<br>
        Администраторская панель.<br>
        Все дополнения для админ-панели являются неофициальными.<br>
        Разработчик - Багданов Илья.<br>
        Все права защищены 2021 год.<br>
    </p>
</div>
<script>
    <?php include_once "./site/scripts/SpoilerController.js"; ?>
</script>
{PLUGIN_FOOTER_JS}
</body>
<?php
$obMain = ob_get_contents();
$obMain = \Engine\PluginManager::IntegrateCSS($obMain);
$obMain = \Engine\PluginManager::IntegrateFooterJS($obMain);
ob_end_clean();
echo $obMain;
?>
</html>

