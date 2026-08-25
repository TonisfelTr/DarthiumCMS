<?php

use Engine\LanguageAgent;
use Engine\Services\Route;
use Users\UserAgent;

Route::create("get", "{id:d}")
     ->setTitling(function ($arguments) {
         return LanguageAgent::getTranslation("user-profile", UserAgent::GetUserNick($arguments['id']));
     })->setName("profile-page-defined");

Route::create("get", "/pm-read/{pm}")
     ->setTitling(function ($arguments) {
         return LanguageAgent::getTranslation("user-profile", UserAgent::GetUserNick($arguments['id']));
     })->setName("profile-page-pm");

Route::create("post", "signup", "site/scripts/handlers/Authorization.php->signUp")
     ->setName("signup")
     ->setCSRFChecking();

Route::create("post", "signin", "site/scripts/handlers/Authorization.php->signIn")
     ->setName("signin")
     ->setCSRFChecking();

Route::create("get", "logout", "site/scripts/handlers/Authorization.php->logout")
     ->setName("logout");

Route::create("get", "activation/{id:d}")
    ->setTitling(fn () => LanguageAgent::getTranslation("mail_activation_topic") .
                          " - " .
                          \Engine\Engine::GetEngineInfo("site.name"))
    ->setName('profile-activation');
