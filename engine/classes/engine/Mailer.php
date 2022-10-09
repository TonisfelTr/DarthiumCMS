<?php

namespace Engine;

class Mailer
{
    public static function SendMail($text, $sendTo, $subject)
    {
        if ($text == "") return false;
        require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/mailer/autoload.php";

        // Create the Transport
        $transport = (new \Swift_SmtpTransport(Engine::GetEngineInfo("ecp") . "://" . Engine::GetEngineInfo("eh"), Engine::GetEngineInfo("ept")))
            ->setUsername(Engine::GetEngineInfo("el"))
            ->setPassword(Engine::GetEngineInfo("ep"));

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);

        // Create a message
        $message = (new \Swift_Message($subject))
            ->setFrom([Engine::GetEngineInfo("el") => LanguageManager::GetTranslation("postman.administration") . ' "' . Engine::GetEngineInfo("sn") . '"'])
            ->setTo([$sendTo])
            ->setBody($text, "text/html");
        $message->addPart(strip_tags($text), "text/plain");

        // Send the message
        $result = $mailer->send($message);
        return $result;
    }
}