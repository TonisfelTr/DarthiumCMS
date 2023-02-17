<?php

namespace Users;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Engine\LanguageManager;
use Engine\Mailer;
use Engine\Uploader;
use Exceptions\Exemplars\InvalidAvatarFileError;
use Exceptions\Exemplars\InvalidEmailError;
use Exceptions\Exemplars\InvalidNicknameError;
use Exceptions\Exemplars\InvalidPictureSizeError;
use Exceptions\Exemplars\InvalidUserCredentialsError;
use Exceptions\Exemplars\NotActivatedUserError;
use Exceptions\Exemplars\ReferrerNotExistError;
use Exceptions\Exemplars\UserExistsError;
use Forum\ForumAgent;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\Services\Session;

class UserAgent {
    private static function IsValidNick($str) {
        if (strlen($str) > 16) {
            return false;
        }
        if (strlen($str) < 4) {
            return false;
        }
        if (preg_match("/^[a-zA-Z0-9_]+$/", $str) === 1) {
            return true;
        } else {
            return false;
        }
    }

    private static function IsEmailValid($str) {
        if (strlen($str) < 2) {
            return False;
        }
        if (preg_match("/[a-z0-9A-Z.@\-_]+/", $str) == 1) {
            return True;
        } else {
            return False;
        }
    }

    private static function UpdateLastData($id) {
        $date = date("Y-m-d", Engine::GetSiteTime());
        $time = Engine::GetSiteTime();

        return DataKeeper::Update("tt_users", ["lastip"   => $_SERVER["REMOTE_ADDR"],
            "lastdate" => $date,
            "lasttime" => $time], ["id" => $id]);
    }

    private static function GetTopicsOfUser(int $userId) {
        $result = [];
        $topicsId = DataKeeper::Get("tt_topics", ["id"], ["authorId" => $userId]);
        foreach ($topicsId as $topicId)
            $result[] = $topicId;

        return $result;
    }

    private static function IsWithQuize(int $topicId) {
        return DataKeeper::Get("tt_quizes", ["id"], ["topicId" => $topicId])[0];
    }

    /**
     * Try to authorize with given param and password.
     * @param $param string Email or nickname. If activation is need it's have to be a email.
     * @param $pass string Password for this account.
     * @param bool $passIsHash
     * @return bool
     */
    private static function Authorization($param, $pass, $passIsHash = False) : bool {
        if (Engine::GetEngineInfo("na")) {
            $paramsToEnter = [$param,
                ($passIsHash) ? $pass : hash("sha256", $pass)
            ];

            if (self::IsValidNick($param)) {
                $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`= ? AND `password`= ?", $paramsToEnter);
            }
            if (self::IsEmailExists($param)) {
                $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `email`= ? AND `password`= ?", $paramsToEnter);
            }

            if (isset($autorizationResult["id"]) && !empty($autorizationResult["id"])) {
                if (self::isActivated($autorizationResult["id"]) == false) {
                    throw new NotActivatedUserError("User account is not activated");
                }
                self::UpdateLastData($autorizationResult["id"]);
                return true;
            } else {
                throw new InvalidUserCredentialsError("Invalid identifier or password");
            }
        } else {
            $autorizationResult = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`= ? AND `password`= ?",
                [$param, (($passIsHash) ? $pass : hash("sha256", $pass))]);
            if (isset($autorizationResult["id"])) {
                if (self::isActivated($autorizationResult["id"]) == false) {
                    throw new NotActivatedUserError("User account is not activated");
                }
                self::UpdateLastData($autorizationResult["id"]);
                return true;
            } else {
                throw new InvalidUserCredentialsError("Invalid nickname or password");
            }
        }
    }

    private static function IsActivated($id) {
        $result = DataKeeper::MakeQuery("SELECT `active` FROM `tt_users` WHERE `id`=?", array($id));
        if ($result["active"] == "TRUE") {
            return true;
        } else {
            return false;
        }
    }

    private static function IsIPRegistred($ipaddress) {
        $queryString = DataKeeper::MakeQuery("SELECT count(*) FROM tt_users WHERE `regip` = ?", [$ipaddress]);

        return $queryString["count(*)"] == 0 ? false : $queryString["count(*)"];
    }

    private static function str_replace_once($search, $replace, $text) {
        $pos = strpos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }

    public static function isAuthorized() : bool {
        return (bool)(new Session(FlashSession::getSessionId()))->getContent("uid");
    }

    public static function getCurrentSession() {
        if (isset($_COOKIE["PHPSESSID"])) {
            return new Session(FlashSession::getSessionId());
        }
    }

    public static function AddCookie(string $name, $content, int $lifeTime, string $whereCookieActive = "/") {
        setcookie($name, $content, $lifeTime, $whereCookieActive, Engine::GetEngineInfo("dm"));
    }

    public static function RemoveCookie(string $name, string $whereCookieWasActive = "/") {
        setcookie($name, "", 0, $whereCookieWasActive, Engine::GetEngineInfo("dm"));
    }

    public static function Get10OnlineUsers() {
        $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE NOT `lasttime` < ? LIMIT 0,10", [Engine::GetSiteTime() - 60 * 5], true);
        $res = [];
        foreach ($queryResponse as $user) {
            $res[] = $user["id"];
        }
        return $res;
    }

    public static function IsEmailExists($email) {
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `email` = ?", [$email])["count(*)"] > 0
            ? true : false;
    }

    public static function IsNicknameExists($nickname) {
        $sqlResult = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE nickname=?", [$nickname]);
        if ($sqlResult["count(*)"] > 0) {
            return true;
        }
        return false;
    }

    public static function ActivateAccount($id = null, $code) {
        if ($code == "true") {
            return false;
        }

        if ($id != null) {
            $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `id`=? AND `active` = ?", [$id,
                $code]);
        } else {
            $queryResponse = DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `active` = ?", [$code]);
        }
        if ($queryResponse["count(*)"] == 0) {
            return false;
        }

        if ($id != null) {
            DataKeeper::Update("tt_users", ["active" => "TRUE"], ["id" => $id, "active" => $code]);
        } else {
            DataKeeper::Update("tt_users", ["active" => "TRUE"], ["active" => $code]);
        }
        return true;
    }

    /**
     * Check if flash session exists.
     *
     * @return bool Flash session existing result.
     */
    public static function IsSessionContinued() : bool {
        return isset($_COOKIE["PHPSESSID"]);
    }

    /**
     * Start a account session.
     *
     * @param $param string Email or nickname of account.
     * @param $pass string Password
     * @return bool Account authorization result
     */
    public static function SessionCreate(string $param, string $pass) : bool {
        $result = false;

        try {
            $result = self::Authorization($param, $pass);
        } catch (InvalidUserCredentialsError $e) {
            if (Engine::GetEngineInfo("na")) {
                FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.invalid_credentials_when_activation_needs"), FlashSession::MA_ERRORS);
            } else {
                FlashSession::writeIn(LanguageManager::GetTranslation("invalid_credentials_when_activation_does_not_need"), FlashSession::MA_ERRORS);
            }

            return $result;
        } catch (NotActivatedUserError $e) {
            FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.inactive_user"), FlashSession::MA_ERRORS);

            return $result;
        }
        $userId = self::GetUserId($param);
        $session = new Session(FlashSession::getSessionId());
        $session->setContent([
            "uid" => $userId,
            "nickname" => self::GetUserNick($userId),
            "email" => self::GetUserParam($userId, "email"),
            "passhash" => hash("sha256", $pass),
            "hostip" => $_SERVER["REMOTE_ADDR"]
        ]);
        $session->remember();

        return $result;
    }

    /**
     * Continue the session with identifier from cookie.
     *
     * @return bool Continue account authorization result.
     */
    public static function SessionContinue() : bool {
        if (isset($_COOKIE["PHPSESSID"])) {
            $session = new Session(FlashSession::getSessionId());

            //If session is empty it means authorization had not been completed... OR?
            //If session does not contain "hostip" key drop it reauthorization trying.
            if ($session->isEmpty() ||
                (isset($session->getContent()["hostip"]) && $session->getContent()["hostip"] != $_SERVER["REMOTE_ADDR"])) {
                self::SessionDestroy();
                return false;
            }

            $authResult = false;
            $needUserActivating = Engine::GetEngineInfo("na");

            if ($needUserActivating) {
                $fstCredential = $session->getContent()["nickname"];
            } else {
                $fstCredential = $session->getContent()["email"];
            }
            $sndCredential = $session->getContent()["passhash"];

            try {
                $authResult = self::Authorization($fstCredential, $sndCredential, true);
            } catch (InvalidUserCredentialsError $e) {
                if (Engine::GetEngineInfo("na")) {
                    FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.invalid_credentials_when_activation_needs"), FlashSession::MA_ERRORS);
                } else {
                    FlashSession::writeIn(LanguageManager::GetTranslation("invalid_credentials_when_activation_does_not_need"), FlashSession::MA_ERRORS);
                }

                return $authResult;
            } catch (NotActivatedUserError $e) {
                FlashSession::writeIn(LanguageManager::GetTranslation("errors_panel.inactive_user"), FlashSession::MA_ERRORS);

                return $authResult;
            }

            return $authResult;
        }

        return false;
    }

    /**
     * Remove session record.
     *
     * @return bool
     */
    public static function SessionDestroy() {
        $sessionId = FlashSession::getSessionId();
        return (new Session($sessionId))->end();
    }

    public static function IsUserExist($id) {
        if ($id <= 0) {
            return false;
        }
        $res = DataKeeper::exists("tt_users", "id", $id);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public static function AddUser($nick, $password, $email, $referer, $unforce = False, $name = '', $city = '',
                                   $sex = 1) {
        if (!self::IsValidNick($nick)) {
            throw new InvalidNicknameError("Name contains invalid symbols");
        }

        if (!self::IsEmailValid($email)) {
            throw new InvalidEmailError("Email contains invalid symbols");
        }

        if ($referer != '') {
            $referer = self::GetUserId($referer);

            if ($referer === False) {
                throw new ReferrerNotExistError("Referrer with that nickname doesn't exist");
            }
        } else {
            $referer = 0;
        }

        if (Engine::GetEngineInfo("na")) {
            $query = "SELECT count(*) FROM `tt_users` WHERE nickname=? OR email=?";
            $sqlResult = DataKeeper::MakeQuery($query, array($nick, $email));
            if ($sqlResult["count(*)"] > 0) {
                throw new UserExistsError("User with these email or nickname already exists", ErrorManager::EC_FIRST_IDENTIFIER_EXISTS);
            }
        } else {
            if (DataKeeper::exists("tt_users", "nickname", $nick)) {
                throw new UserExistsError("User with that nickname already exists.", ErrorManager::EC_NICKNAME_EXISTS);
            }
        }

        if (Engine::GetEngineInfo("map") == "y") {
            if (self::IsIPRegistred($_SERVER["REMOTE_ADDR"])) {
                throw new UserExistsError("User with this IP already exists", ErrorManager::EC_IP_REGISTERED);
            }
        }

        $randomWord = Engine::RandomGen(10);

        $queryReqRequest = DataKeeper::InsertTo("tt_users", array(
            "nickname" => $nick,
            "password" => hash("sha256", $password),
            "email"    => $email,
            "group"    => Engine::GetEngineInfo("sg"),
            "active"   => (Engine::GetEngineInfo("na") && $unforce != False) ? $randomWord : "TRUE",
            "regdate"  => date("Y-m-d", Engine::GetSiteTime()),
            "regip"    => $_SERVER["REMOTE_ADDR"],
            "avatar"   => "no",
            "referer"  => $referer,
            "city"     => $city,
            "realname" => $name,
            "sex"      => $sex,
            "lastip"   => "null"
        ));
        if ($queryReqRequest) {
            ob_start();
            include_once "../../site/templates/" . Engine::GetEngineInfo("stp") . "/mailbody.html";
            $body = ob_get_contents();
            ob_end_clean();

            $link = ((!empty(Engine::GetEngineInfo("dm"))) ? Engine::GetEngineInfo("dm") : $_SERVER['HTTP_HOST']) .
                "/profile.php?activate=$randomWord&uid=" . UserAgent::GetUserId($nick);
            if (Engine::GetEngineInfo("na") && $unforce != false) {
                $bodyMain = LanguageManager::GetTranslation("mail_need_activation");
                $bodyMain = str_replace("{EMAIL:ACTIVATION_LINK}", $link, $bodyMain);
                $bodyMain = self::str_replace_once("{EMAIL:NICKNAME}", $nick, $bodyMain);
                $bodyMain = self::str_replace_once("{EMAIL:ACTIVATION_CODE}", $randomWord, $bodyMain);
                $body = str_replace("{MAIL_TITLE}", LanguageManager::GetTranslation("mail_activation_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn"), $body);
                $body = str_replace("{MAIL_NICKNAME_TO}", LanguageManager::GetTranslation("mail_hello") . " " . $nick . "!", $body);
                $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                $body = str_replace("{MAIL_FOOTER_INFORMATION}", LanguageManager::GetTranslation("mail_administrators_signature") . " \"" . Engine::GetEngineInfo("sn") . "\"<br>"
                    . LanguageManager::GetTranslation("copyright"), $body);
                if (!Mailer::SendMail($body, $email, LanguageManager::GetTranslation("mail_activation_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"")) {
                    DataKeeper::Delete("tt_users", ["nickname" => $nick]);
                    return false;
                } else {
                    if ($referer !== false) {
                        $notificator = new Notificator($referer);
                        $notificator->createNotify(14, $queryReqRequest);
                    }
                }
            } else {
                $bodyMain = LanguageManager::GetTranslation("mail_just_info");
                $bodyMain = str_replace("{EMAIL:NICKNAME}", $nick, $bodyMain);
                $bodyMain = str_replace("{EMAIL:PASSWORD}", $password, $bodyMain);
                $body = str_replace("{MAIL_TITLE}", LanguageManager::GetTranslation("mail_registration_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"", $body);
                $body = str_replace("{MAIL_SITENAME}", Engine::GetEngineInfo("sn"), $body);
                $body = str_replace("{MAIL_NICKNAME_TO}", $nick, $body);
                $body = str_replace("{MAIL_BODY_MAIN}", $bodyMain, $body);
                $body = str_replace("{MAIL_FOOTER_INFORMATION}", LanguageManager::GetTranslation("mail_administrators_signature") . " \"" . Engine::GetEngineInfo("sn") . "\"<br>"
                    . LanguageManager::GetTranslation("copyright"), $body);
                if (!Mailer::SendMail($body, $email, LanguageManager::GetTranslation("mail_registration_topic") . " \"" . Engine::GetEngineInfo("sn") . "\"")) {
                    DataKeeper::Delete("tt_users", ["nickname" => $nick]);
                    return false;
                } else {
                    if ($referer !== false) {
                        $notificator = new Notificator($referer);
                        $notificator->createNotify(14, $queryReqRequest);
                    }
                }
            }
            return true;
        }
        return false;

    }

    public static function DeleteUser($id) {
        /* These things must be deleted:
         * 1. Notifications
        */
        DataKeeper::Delete("tt_notifications", ["type" => 20, "subject" => $id]);
        DataKeeper::Delete("tt_notifications", ["fromUid" => $id]);
        DataKeeper::Delete("tt_notifications", ["toUid" => $id]);
        /* 2. Topics */
        $topics = self::GetTopicsOfUser($id);
        for ($i = 0; $i < count($topics); $i++) {
            if (self::IsWithQuize($topics[$i]) > 0) {
                ForumAgent::DeleteQuize(self::IsWithQuize($topics[$i]));
            }
        }
        DataKeeper::Delete("tt_topics", ["authorId" => $id]);
        /* 3. Comments */
        DataKeeper::Delete("tt_topiccomments", ["authorId" => $id]);
        /* 4. Reports */
        DataKeeper::Delete("tt_reports", ["author" => $id]);
        /* 5. Answers in reports. */
        DataKeeper::Delete("tt_reportda", ["addedUID" => $id]);
        DataKeeper::Delete("tt_reportanswers", ["authorId" => $id]);
        /* 6. Friend */
        DataKeeper::Delete("tt_friends", ["fhost" => $id]);
        DataKeeper::Delete("tt_friends", ["friendId" => $id]);
        /* 7. PMs */
        DataKeeper::Delete("tt_pmessages", ["senderUID" => $id]);
        DataKeeper::Delete("tt_pmessages", ["receiverUID" => $id]);
        /* 8. Blacklisted. */
        DataKeeper::Delete("tt_blacklisted", ["authorId" => $id]);
        DataKeeper::Delete("tt_blacklisted", ["blockId" => $id]);
        DataKeeper::Delete("tt_topicsmarks", ["userId" => $id]);
        /* 9. Uploaded files */
        Uploader::DeleteFilesOfUser($id);
        if (UserAgent::GetUserParam($id, "avatar") != "no") {
            unlink("../uploads/avatars/" . UserAgent::GetUserParam($id, "avatar"));
        }
        /* 10.Additional fields */
        DataKeeper::Delete("tt_adfieldscontent", ["userId" => $id]);
        ///////////////////////////////////////////////////////////////////////////////////
        DataKeeper::Delete("tt_users", ["id" => $id]);
    }

    public static function GetAllUsers() {
        return DataKeeper::Get("tt_users", ["id", "nickname"]);
    }

    public static function GetUsersList(array $paramsArray, $page = 1) {
        $lowBorder = $page * 50 - 50;
        $highBorder = $page * 50;

        if (count($paramsArray) == 0) {
            $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` LIMIT $lowBorder, $highBorder", [], true);
        } else {
            if (isset($paramsArray["nickname"])) {
                $paramsArray["nickname"] = str_replace("*", "%", $paramsArray["nickname"]);
            }
            if (isset($paramsArray["email"])) {
                $paramsArray["email"] = str_replace("*", "%", $paramsArray["email"]);
            }
            if (isset($paramsArray["lastip"])) {
                $paramsArray["lastip"] = str_replace("*", "%", $paramsArray["lastip"]);
            }
        }
        if (count($paramsArray) != 0) {
            if (!isset($paramsArray["nickname"])) {
                $paramsArray["nickname"] = "%";
            }
            if (!isset($paramsArray["email"])) {
                $paramsArray["email"] = "%";
            }
            if (!isset($paramsArray["lastip"])) {
                $paramsArray["lastip"] = "%";
            }
            if (!isset($paramsArray["referer"])) {
                $paramsArray["referer"] = "%";
            }
            if (!isset($paramsArray["group"])) {
                $paramsArray["group"] = "%";
            }

            if (isset($paramsArray["referer"])) {
                if (UserAgent::GetUserId($paramsArray["referer"])) {
                    $paramsArray["referer"] = UserAgent::GetUserId($paramsArray["referer"]);
                }

            }
            $queryResponse = DataKeeper::MakeQuery("SELECT
                                                                    `id`
                                                                FROM
                                                                    `tt_users`
                                                                WHERE
                                                                    `nickname` LIKE ?
                                                                AND `email` LIKE ?
                                                                AND `lastip` LIKE ?
                                                                AND `referer` LIKE ?
                                                                AND `group` LIKE ?
                                                                LIMIT $lowBorder, $highBorder",
                [$paramsArray["nickname"],
                    $paramsArray["email"],
                    $paramsArray["lastip"],
                    $paramsArray["referer"],
                    $paramsArray["group"]],
                true);
        }

        return $queryResponse;
    }

    public static function GetUsersCount() {
        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users`")["count(*)"];
    }

    public static function ChangeUserParams($id, $param, $newparam) {
        if ($param == 'password' || $param == 'id') {
            return false;
        }

        if (!self::IsUserExist($id)) {
            throw new UserExistsError("This user doesn't exist", 7);
        }

        if ($param == "nickname") {
            if (!self::IsValidNick($newparam)) {
                throw new InvalidNicknameError("Invalid nickname");
            }

            if (self::IsNicknameExists($newparam)) {
                throw new UserExistsError("User with that nickname already exists", 4);
            }
        }

        if ($param == "email") {
            if (!self::IsEmailValid($newparam)) {
                 throw new InvalidEmailError("Invalid email");
            }

            if (Engine::GetEngineInfo("na") && self::IsEmailExists($newparam)) {
                throw new UserExistsError("User with that email already exists", 34);
            }
        }

        return DataKeeper::Update("tt_users", array($param => $newparam), array("id" => $id));
    }

    public static function ChangeUserPassword($id, $newPass, bool $relogin) {
        if (!self::IsUserExist($id)) {
            throw new UserExistsError("User does not exist", 7);
        }

        $resp = DataKeeper::Update("tt_users", array("password" => hash("sha256", $newPass)), array("id" => $id));
        if ($relogin) {
            self::SessionDestroy();
            self::SessionCreate(UserAgent::GetUser($id)->getEmail(), $newPass);
        }
        return $resp;
    }

    public static function GetUserId($param) {
        return DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname`=? OR `email` = ?", [$param,
            $param])["id"];
    }

    public static function GetUserNick($id) {
        if (self::IsUserExist($id) === false) {
            throw new UserExistsError("User does not exist", 7);
        }

        return DataKeeper::Get("tt_users", ["nickname"], ["id" => $id])[0]["nickname"];
    }

    public static function GetUserGroupId($idUser) {
        if (!self::IsUserExist($idUser)) {
            throw new UserExistsError("User does not exist", 7);
        }

        return DataKeeper::Get("tt_users", ["group"], ["id" => $idUser])["group"];
    }

    public static function GetUser($idUser) {
        if (!UserAgent::IsUserExist($idUser)) {
            return false;
        }
        return new User($idUser);
    }

    public static function GetUserRefererForCount($idUser) {
        if (!self::IsUserExist($idUser)) {
            throw new UserExistsError("User does not exist", 7);
        }

        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `referer` = ?", [$idUser])["count(*)"];
    }

    /**
     * Return content of user property.
     * @param $idUser int User ID
     * @param $param string Property that should be returned.
     * @return bool|int|array
     */
    public static function GetUserParam($idUser, $param) {
        if (in_array($param, array(0 => 'nickname',
            1 => 'id',
            2 => 'password',
            3 => 'group'))) {
            return false;
        }

        if (!self::IsUserExist($idUser)) {
            throw new UserExistsError("User does not exist", 7);
        }
        return DataKeeper::Get("tt_users", [$param], ['id' => $idUser])[0][$param];
    }

    /**
     * Return a array with ids users have a nickname like a schedule.
     * @param $Snickname string Schedule of nickname.
     * @return array|int
     * In shedule you can use * symbol for unknown substring.
     */
    public static function FindUsersBySNickname($Snickname) {
        if (strstr($Snickname, "*") > -1) {
            $Snickname = str_replace("*", "%", $Snickname);
        }
        $result = [];
        $queryResponse = DataKeeper::MakeQuery("SELECT `id` FROM `tt_users` WHERE `nickname` LIKE ?", [$Snickname]);
        foreach ($queryResponse as $Sid) {
            $result[] = $Sid;
        }
        return $result;
    }

    public static function UploadAvatar($idUser, $fileFormName) {
        if (!UserAgent::IsUserExist($idUser)) {
            throw new UserExistsError("User does not exist", 7);
        }

        $imgtypes = array(
            0 => "jpg",
            1 => "png",
            2 => "tif",
            3 => "jpeg",
            4 => "gif"
        );

        echo Uploader::ExtractType($_FILES[$fileFormName]['name']) . "<br>";
        echo $_FILES[$fileFormName]['name'];
        if (in_array(Uploader::ExtractType($_FILES[$fileFormName]['name']), $imgtypes)) {
            $uploaddir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/avatars/";
        } else {
            throw new InvalidAvatarFileError("Failed uploading the avatar file", 18);
        }

        if (getimagesize($_FILES[$fileFormName]['tmp_name'])[0] != Engine::GetEngineInfo("aw") ||
            getimagesize($_FILES[$fileFormName]['tmp_name'])[1] != Engine::GetEngineInfo("ah")) {
            throw new InvalidPictureSizeError("This avatar picture is too big", 19);
        }

        /** todo: поправить разме; в админке можно указать коэфициент вместо шестёрки. */
        if ($_FILES[$fileFormName]['size'] > 6 * 1024 * 1024) {
            throw new InvalidAvatarFileError("Picture has too big sizes", 20);
        }

        if (file_exists("../uploads/avatars/" . UserAgent::GetUserParam($idUser, "avatar"))) {
            unlink("../uploads/avatars/" . UserAgent::GetUserParam($idUser, "avatar"));
        }

        $newName = $idUser . "." . Uploader::ExtractType(basename($_FILES[$fileFormName]['name']));
        $uploadfile = $uploaddir . $newName;

        $glob = glob($uploaddir . $idUser . ".*");
        if (count($glob) != 0) {
            $reset = reset($glob);
            unlink($reset);
        }

        if (!is_uploaded_file($_FILES[$fileFormName]['tmp_name'])) {
            echo "Something wrong, file was not uploaded!";
        }

        if (!move_uploaded_file($_FILES[$fileFormName]['tmp_name'], $uploadfile)) {
            return False;
        }

        return DataKeeper::Update("tt_users", ["avatar" => $newName], ["id" => $idUser]);
    }

    public static function ClearNotifications() {
        return DataKeeper::MakeQuery("DELETE FROM `tt_notifications` WHERE `createTime` < ?", [Engine::GetSiteTime() - 30 * 24 * 60 * 60]);
    }

    public static function GetOnlineFriendsCount($userId) {
        if (!self::IsUserExist($userId)) {
            return false;
        }

        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_users` WHERE `id` IN (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `lasttime` > ?", [$userId,
            Engine::GetSiteTime() - 60 * 15])["count(*)"];
    }

    public static function GetOnlineFriends($ofUserId) {
        if (!self::IsUserExist($ofUserId)) {
            return false;
        }
        return DataKeeper::MakeQuery("SELECT `users`.`id` AS `friendId`,
                                                   `friends`.`fhost` AS `fhost`,
                                                   `friends`.`regdate` AS `regdate`            
                                             FROM `tt_users` AS `users`
                                             LEFT JOIN `tt_friends` AS `friends` ON `users`.`id` = `friends`.`friendId`   
                                             WHERE `users`.`id` IN (SELECT `friendId` FROM `tt_friends` WHERE `fhost`=?) AND `users`.`lasttime` > ?",
            [$ofUserId, Engine::GetSiteTime() - 60 * 15], true);
    }

    public static function GetAdditionalFieldsList() {
        return DataKeeper::Get("tt_adfields", ["*"]);
    }

    public static function GetAdditionalFieldsListOfUser($userId) {
        $result = [];
        $data = DataKeeper::Get("tt_adfieldscontent", array("fieldId",
            "content",
            "isPrivate"), array("userId" => $userId));
        foreach ($data as $d) {
            $result[$d["fieldId"]] = $d;
        }
        return $result;
    }

    public static function GetAdditionalFieldContentOfUser($userId, $fieldId) {
        return DataKeeper::Get("tt_adfieldscontent", ["content"], ["userId" => $userId, "fieldId" => $fieldId])[0];
    }

    public static function SetAdditionalFieldContent($userId, $fieldId, $content) {
        if (DataKeeper::existsWithConditions("tt_adfieldscontent", array("fieldId" => $fieldId, "userId" => $userId))) {
            $request = DataKeeper::Update("tt_adfieldscontent", array("content" => $content), array("fieldId" => $fieldId,
                "userId"  => $userId));
        } else {
            $request = DataKeeper::InsertTo("tt_adfieldscontent", array("userId"  => $userId,
                "fieldId" => $fieldId,
                "content" => $content));
        }
        return $request;
    }

    public static function SetPrivacyToAdditionalField($userId, $fieldId, $privacy) {
        $request = DataKeeper::Update("tt_adfieldscontent", array("isPrivate" => $privacy), array("fieldId" => $fieldId,
            "userId"  => $userId));
        return $request;
    }
}