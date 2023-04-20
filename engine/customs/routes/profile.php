<?php

use Engine\LanguageManager;
use Engine\Services\Route;
use Users\UserAgent;

Route::create("get", "{id}")
    ->setTitling(function ($arguments) {
        return LanguageManager::GetTranslation("user-profile", UserAgent::GetUserNick($arguments['id']));
    })->setName("profile-page-defined");