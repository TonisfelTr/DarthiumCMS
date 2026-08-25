<?php

namespace Site\Scripts\Handlers;

use Engine\Engine;
use Engine\LanguageAgent;
use Engine\Services\Route;
use Engine\Services\RouteHandler;
use Engine\Traits\Validation;
use Guards\CaptchaMen;
use Users\Services\FlashSession;
use Users\UserAgent;

return new class extends RouteHandler {
    use Validation;

    public function signUp(Route $currentRoute): bool {
        if (!$this->runValidation([
            "nickname" => "required,nickname",
            "password" => "required,password,confirm",
            "email"    => "required,email",
            "referrer" => "nickname",
        ])) {
            return $this->with(["registration" => true])
                        ->goBack();
        }

        if (CaptchaMen::CheckCaptcha())

            UserAgent::AddUser(
                $currentRoute->getParameter("nickname"),
                $currentRoute->getParameter("password"),
                $currentRoute->getParameter("email"),
                $currentRoute->getParameter("referrer"),
                false,
                $currentRoute->getParameter("name"),
                $currentRoute->getParameter("city"),
                $currentRoute->getParameter("sex")
            );

        return $this->withInfos([
            "message" =>
                Engine::GetEngineInfo("account.need_activate") ? LanguageAgent::getTranslation("errors_panel.registration_success_with_activation")
                    : LanguageAgent::getTranslation("errors_panel.registration_success_without_activation")
        ])
                    ->redirectByRoute("profile-page");
    }

    public function signIn(Route $currentRoute): bool {
        if (!$this->runValidation([
            "login"    => "required,nickname",
            "password" => "required,password"
        ])) {
            return $this->with(["authorization" => true])
                        ->goBack();
        }

        if (!UserAgent::SessionCreate($currentRoute->getParameter("login"), $currentRoute->getParameter("password"))) {
            return $this->with(["authorization" => true])
                        ->goBack();
        }

        return $this->redirectByRoute("profile-page");
    }

    public function logout(Route $currentRoute): bool {
        UserAgent::SessionDestroy();

        return $this->redirectByRoute("profile-page");
    }
};
