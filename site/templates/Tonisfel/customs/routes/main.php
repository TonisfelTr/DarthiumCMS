<?php

use Engine\LanguageAgent;
use Engine\PluginAgent;
use Engine\Services\Route;
use Forum\ForumAgent;
use Forum\Models\Topic;
use Forum\StaticPagesAgent;
use Users\GroupAgent;
use Users\UserAgent;

Route::create("get", "/page/{page_number:d}")
     ->setTitling(function ($arguments) {
         return LanguageAgent::getTranslation("home-page-number") . $arguments['page_number'];
     })
     ->setName("main-page-paginated");

Route::create("get", "newtopic")
     ->setName("new-topic");

Route::create("get", "sp/{id:d}")
     ->setTitling(function ($arguments) {
         return StaticPagesAgent::GetPage($arguments['id'])->getPageName();
     })
     ->setName("static-page");

Route::create("get", "search/name")
     ->setParameters(["s"])
     ->setName("topic-searching-by-name");

Route::create("get", "search/author")
     ->setParameters(["s"])
     ->setName("topic-searching-by-author");
Route::create("get", "search/quiz")
     ->setParameters(["s"])
     ->setName("topic-searching-by-quiz");

Route::create("get", "groups/{id:d}")
    ->setAvailableValuesOfChainLink("{id}", GroupAgent::getGroupList())
    ->setTitling(function ($arguments) {
        $groupName = GroupAgent::getGroupNameById($arguments['id']);
         return LanguageAgent::getTranslation("grouplist.view", $groupName);
     })
     ->setName("group-list");

Route::create("get", "plugin/{plugin_name:b}")
    ->setAvailableValuesOfChainLink("{plugin_name}", PluginAgent::getInstalledPluginsNames())
     ->setName("plugin-page");

Route::create("get", "category/{category_id:d}/{page:nd}")
    ->setAvailableValuesOfChainLink("{category_id}", ForumAgent::getCategoryList())
     ->setTitling(function ($arguments) {
         $categoryName = ForumAgent::getCategoryParam($arguments['category_id'], 'name');
         return LanguageAgent::getTranslation("category") . " \"$categoryName\"";
     })
     ->setName("category-topics");

Route::create("get", "topic/{id:d}/{page:nd}")
     ->setTitling(function ($arguments) {
         return (new Topic($arguments['id']))->getName();
     })
     ->setAccessCondition(function ($arguments) {
         $topic = new Topic($arguments['id']);
         $user  = UserAgent::getCurrentUser();

         return $topic->getCategory()->isPublic()
                || (!$topic->getCategory()->isPublic() && $user !== false
                    && $user->getUserGroup()->getPermission("category_see_unpublic"));
     })
     ->setName("topic-view");
Route::create("get", "topic/{id:d}/edit")
     ->setAccessCondition(function ($arguments) {
     })
     ->setTitling(function ($arguments) {
     })
     ->setName("topic-edit");

Route::create("get", "rules")
     ->setTitling("Правила")
     ->setName("rules-page");