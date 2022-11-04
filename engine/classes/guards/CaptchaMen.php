<?php

namespace Guards;

use Engine\DataKeeper;
use Engine\Engine;
use Engine\ErrorManager;
use Exceptions\Exemplars\CaptchaGenerationError;
use Exceptions\Exemplars\CaptchaNotExistError;
use Exceptions\Exemplars\ForbiddenError;

class CaptchaMen{
    private static $captchaHash;
    private static $captchaIDHash;
    private static $captchaFetched = False;
    private static $captchaType;

    private static function GetCaptcha($id, $type){
        if (empty($id)) exit;

        $queryResponse = DataKeeper::Get("tt_captcha", ["captcha"], ["id_hash" => $id, "type" => $type])[0]["captcha"];
        if ($queryResponse != '') return $queryResponse;
        else return false;
    }

    public static function GenerateCaptcha(){

        self::$captchaFetched = False;
        $captcha = Engine::RandomGen();
        self::$captchaIDHash = hash("sha1", Engine::RandomGen());
        if(self::$captchaHash = $captcha)
            if (!empty(self::$captchaHash)) return self::$captchaIDHash;
            else return False;

    }

    public static function FetchCaptcha($type){

        /* Types:
         * 1. Registration
         * 2. Authorization
         * 3. Send message.
         * 4. Reputation change
         */
        if (empty(self::$captchaHash) || empty($type)){
            throw new CaptchaNotExistError("Cannot find captcha string");
        }

        self::$captchaType = $type;
        $imageName = Engine::RandomGen(8);

        DataKeeper::InsertTo("tt_captcha", ["id_hash" => self::$captchaIDHash,
            "captcha" => strtoupper(self::$captchaHash),
            "type" => $type,
            "createTime" => Engine::GetSiteTime(),
            "picName" => $imageName]);
        self::$captchaFetched = True;
        return $imageName;

    }

    public static function GenerateImage($imageName){
        if (empty(self::$captchaIDHash) || self::$captchaFetched == False){
            throw new CaptchaNotExistError("Cannot find captcha string");
        }

        if (!$image = imagecreatetruecolor(100,35)){
            throw new CaptchaGenerationError("Failed to generate captcha picture", 8);
        }

        imagefill($image, 0, 0, imagecolorallocate($image, 255,255,255));
        for($i = 0; $i <= 8; $i++)
            imageline($image, rand(0, 35), rand(0, 35), rand(0, 100), rand(0, 100), imagecolorallocate($image, rand(0,255),rand(0,255),rand(0,255)));
        imagettftext($image, 12, 0, 8, 23, imagecolorallocate($image, 0x00, 0x00, 0x00), $_SERVER["DOCUMENT_ROOT"]."/engine/captchas/font.ttf", CaptchaMen::$captchaHash);
        imagepng($image, $_SERVER["DOCUMENT_ROOT"]."/engine/captchas/".$imageName.".png");
        return "/engine/captchas/".$imageName.".png";
    }

    public static function CheckCaptcha($typedCaptcha, $captchaID, $type){
        if (empty($captchaID) || empty($type) || empty($typedCaptcha)) return false;

        return DataKeeper::MakeQuery("SELECT count(*) FROM `tt_captcha` WHERE `type` = ? AND `captcha` LIKE ? AND `id_hash` = ?", [$type, strtoupper($typedCaptcha), $captchaID])["count(*)"] == 0 ? false : true;
    }

    public static function RemoveCaptcha(){
        $time = Engine::GetSiteTime() - 600;
        $queryResponse = DataKeeper::MakeQuery("SELECT `picName` FROM `tt_captcha` WHERE `createTime` < ?", [$time], true);

        foreach ($queryResponse as $captcha) {
            if (!unlink("../engine/captchas/" . $captcha["picName"] . ".png"))
                continue;
        }

        return DataKeeper::Delete("tt_captcha", ["createTime" => $time]);
    }
}