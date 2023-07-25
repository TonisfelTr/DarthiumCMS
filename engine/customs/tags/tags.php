<?php

use Builder\Controllers\BannerAgent;
use Builder\Controllers\BuildManager;
use Builder\Controllers\NavbarAgent;
use Builder\Controllers\SidePanelsAgent;
use Builder\Controllers\TagAgent;
use Builder\Models\SidePanel;
use Builder\Services\Tag;
use Engine\Engine;
use Engine\LanguageManager;
use Engine\PluginManager;
use Engine\RouteAgent;
use Exceptions\TavernException;
use Forum\ForumAgent;
use Forum\Models\Topic;
use Forum\StaticPagesAgent;
use Users\Models\Group;
use Users\UserAgent;

$sidePanels   = SidePanelsAgent::GetPanelsList();
$navbarEndUls = "";
$user         = UserAgent::isAuthorized() ? UserAgent::getCurrentUser() : false;

Tag::create("{INDEX_PAGE_NAVBAR}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("navbar.html");
});
Tag::create("{INDEX_NAVBAR_BTNS}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("nav-btn.html");
});
Tag::create("{END_NAVBAR_BTNS}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("nav-btn-end.html");
});
Tag::create("{INDEX_PROFILE_MENU}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile((UserAgent::isAuthorized() ? "pam_auth" : "pam_unauth") . ".html");
});
Tag::create("{INDEX_PROFILE_MENU:MAIL_SPAN_CLASS}")->setProcessingFunction(function () use ($user) {
    return $user === false ? "" : "nav-btn-" . (($user->MessageManager()->getNotReadCount() > 0) ? "new-" : "") . "counter";
});
Tag::create("{INDEX_PROFILE_MENU:MAIL_SPAN_COUNT}")->setProcessingFunction(function () use ($user) {
    return $user === false
        ? ""
        : (($user->MessageManager()->getNotReadCount() > 10)
            ? "10+"
            : "{$user->MessageManager()->getNotReadCount()}");
});
Tag::create("{INDEX_PROFILE_MENU:NOTIF_SPAN_CLASS}")->setProcessingFunction(function () use ($user) {
    return $user === false ? "" : "nav-btn-" . (($user->Notifications()->getNotificationsUnreadCount()) ? "new-"
            : "") . "counter";
});
Tag::create("{INDEX_PROFILE_MENU:NOTIF_SPAN_COUNT}")->setProcessingFunction(function () use ($user) {
    return $user === false
        ? ""
        : (($user->Notifications()->getNotificationsUnreadCount() > 10) ? "10+"
            : "{$user->Notifications()->getNotificationsUnreadCount()}");
});
Tag::create("{INDEX_PROFILE_MENU:USER_NICKNAME}")->setProcessingFunction(function () use ($user) {
    return $user ? $user->getNickname() : "{%|profile}";
});
Tag::create("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}")->setProcessingFunction(function () use ($user) {
    return $user && $user->getUserGroup()->getPermission("enterpanel")
        ? '<li><a href="{>|adminpanel-page}">{%|adminpanel-link}</a>'
        : '';
});
Tag::create("{INDEX_CATEGORY_LIST}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("category_list.html");
});
Tag::create("{INDEX_PAGE_HEADER}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("header.html");
});
Tag::create("{INDEX_PAGE_FOOTER}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("footer.html");
});
Tag::create("{CURRENT_YEAR}")->setProcessingFunction(function () {
    return date("Y");
});
Tag::create("{INDEX_PAGE_LEFT}")->setProcessingFunction(function () use ($sidePanels) {
    $sidePanelHTML = BuildManager::includeContent("leftside.html", TEMPLATE_ROOT);
    $resultHTML    = "";
    foreach ($sidePanels as $sidePanel) {
        $panel = new SidePanel($sidePanel["id"]);
        if ($panel->getVisibility()) {
            if ($panel->getType() == "leftside") {
                $leftPanel  = BuildManager::replaceOnceInString("{PANEL_TITLE}", $panel->getName(), $sidePanelHTML);
                $leftPanel  = BuildManager::replaceOnceInString("{PANEL_CONTENT}", $panel->getContent(), $leftPanel);
                $resultHTML .= $leftPanel;
            }
        }
    }

    return $resultHTML;
});
Tag::create("{INDEX_PAGE_RIGHT}")->setProcessingFunction(function () use ($sidePanels) {
    $sidePanelHTML = BuildManager::includeContent("rightside.html", TEMPLATE_ROOT);
    $resultHTML    = "";
    foreach ($sidePanels as $sidePanel) {
        $panel = new SidePanel($sidePanel["id"]);
        if ($panel->getVisibility()) {
            if ($panel->getType() == "rightside") {
                $rightPanel = BuildManager::replaceOnceInString("{PANEL_TITLE}", $panel->getName(), $sidePanelHTML);
                $rightPanel = BuildManager::replaceOnceInString("{PANEL_CONTENT}", $panel->getContent(), $rightPanel);
                $resultHTML .= $rightPanel;
            }
        }
    }

    return $resultHTML;
});
Tag::create("{LAST_SITE_TOPICS}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("last_topic_list.html");
});
Tag::create("{STATISTIC_NOW_AVAILABLE}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile("online_list.html");
});
Tag::create("{MAIN_PAGE:FIRST_BIG_BANNER}")->setProcessingFunction(function () {
    $bigBanners      = BannerAgent::GetBanners("banner");
    $bigBannersCount = count($bigBanners);
    if ($bigBannersCount > 0) {
        $bigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
        $bigBanner = '<img class="img-bgbanner" src="{>|banners:' . $bigBanner . '}">';
    }
    else {
        $bigBannerUrl = RouteAgent::buildRoute("banners", "bigbanner.png");
        $bigBanner    = '<img class="img-bgbanner" src="' . $bigBannerUrl . '" title="{%|ad_is_free}">';
    }
    return $bigBanner;
});
Tag::create("{MAIN_PAGE:SECOND_BIG_BANNER}")->setProcessingFunction(function () {
    $bigBanners      = BannerAgent::GetBanners("banner");
    $bigBannersCount = count($bigBanners);
    if ($bigBannersCount > 0) {
        $bigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
        $bigBanner = '<img class="img-bgbanner" src="{>|banners:' . $bigBanner . '}">';
    }
    else {
        $bigBannerUrl = RouteAgent::buildRoute("banners", "bigbanner.png");
        $bigBanner    = '<img class="img-bgbanner" src="' . $bigBannerUrl . '" title="{%|ad_is_free}">';
    }
    return $bigBanner;
});
Tag::create("{MAIN_PAGE:FOOTER_FIRST_SMALL_BANNER}")->setProcessingFunction(function () {
    $firstBanner = BannerAgent::GetBannersByName("firstbanner");
    if (empty($firstBanner)) {
        $firstBanner = '<img class="img-smbanner" src="{>|banners:smallbanner.png}" title="{%|ad_is_free}">';
    }
    else {
        $firstBanner = '<div class="img-smbanner">' . $firstBanner['content'] . '</div>';
    }

    return $firstBanner;
});
Tag::create("{MAIN_PAGE:FOOTER_SECOND_SMALL_BANNER}")->setProcessingFunction(function () {
    $secondBanner = BannerAgent::GetBannersByName("secondbanner");
    if (empty($secondBanner)) {
        $secondBanner = '<img class="img-smbanner" src="{>|banners:smallbanner.png}" title="{%|ad_is_free}">';
    }
    else {
        $secondBanner = "<div class=\"img-smbanner\">" . $secondBanner['content'] . "</div>";
    }

    return $secondBanner;
});
Tag::create("{INDEX_CATEGORY_HINT}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile('category_hint.html');
});
Tag::create("{INDEX_MESSAGE_BOX}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile('messagebox.html');
});
Tag::create("{INDEX_SEARCHING}")->setProcessingFunction(function () {
    return BuildManager::includeFromTemplateAndCompile('searchpanel.html');
});
Tag::create("{INDEX_SEARCHING_TEXT}")->setProcessingFunction(function () {
    if (in_array(RouteAgent::getCurrentRoute()->getName(), ['topic-searching-by-name', 'topic-searching-by-author', 'topic-searching-by-quiz'])) {
        return RouteAgent::getCurrentRoute()->getParameter('s', true);
    }
    else {
        return '';
    }
});
Tag::create("{INDEX_SEARCHING_TYPE}")->setProcessingFunction(function () {
    if (RouteAgent::getCurrentRoute()->getName() == 'topic-searching-by-author') {
        return '$("button#search-by-author").click()';
    }
    elseif (RouteAgent::getCurrentRoute()->getName() == 'topic-searching-by-quiz') {
        return '$("button#search-by-quiz").click()';
    }

    return '';
});
Tag::create("{INDEX_PAGE_MAIN_BLOCK}", true)
   ->setProcessingFunction(function () {
       $currentRoute = RouteAgent::getCurrentRoute();
       switch ($currentRoute->getName()) {
           case "new-topic":
               return BuildManager::includeContent("site/newtopic.php");
           case "special-page":
               $specialPageId = $currentRoute->getValueOfChainLink("id");
               return Engine::CompileBBCode(file_get_contents(HOME_ROOT . "site/statics/" . $specialPageId . ".txt", FILE_USE_INCLUDE_PATH));
           case "topic-view":
               return BuildManager::includeFromTemplate("news/new.html");
           case "topic-searching-by-name":
               return BuildManager::includeContent("site/search.php");
           case "group-list":
           {
               return BuildManager::includeFromTemplateAndCompile("grouptable.html");
           }
           case "plugin-page":
               return BuildManager::includeFromPlugin($currentRoute->getValueOfChainLink("plugin_page"), "bin/engine.php");
           case "main-page-paginated":
           {
               return TagAgent::useTemporaryContainer(function () use ($currentRoute) {
                   $result = '';

                   $newsTemplate = BuildManager::includeFromTemplateAndCompile("news/preview.html");
                   if (!TagAgent::temporaryContainerFilled()) {
                       $topicAuthorAvatarTag     = Tag::create("{TOPIC_AUTHOR_AVATAR}");
                       $topicAuthorIdTag         = Tag::create("{TOPIC_AUTHOR_ID}");
                       $topicAuthorNicknameTag   = Tag::create("{TOPIC_AUTHOR_NICKNAME}");
                       $topicAuthorGroupColorTag = Tag::create("{TOPIC_AUTHOR_GROUP_COLOR}");
                       $topicAuthorGroupNameTag  = Tag::create("{TOPIC_AUTHOR_GROUP_NAME}");
                       $topicAuthorGroupIdTag    = Tag::create("{TOPIC_AUTHOR_GROUP_ID}");
                       $topicLikesCountTag       = Tag::create("{TOPIC_LIKES_COUNT}");
                       $topicDislikesCountTag    = Tag::create("{TOPIC_DISLIKES_COUNT}");
                       $topicMarksCountTag       = Tag::create("{TOPIC_MARKS_COUNT}");
                       $topicNameTag             = Tag::create("{TOPIC_NAME}");
                       $topicBodyTag             = Tag::create("{TOPIC_BODY}");
                       $topicStatusIconTag       = Tag::create("{TOPIC_STATUS_ICON}");
                       $topicCategoryTag         = Tag::create("{TOPIC_CATEGORY}");
                       $topicIdTag               = Tag::create("{TOPIC_ID}");
                   }

                   $topicsList = ForumAgent::GetTopicList($currentRoute->getValueOfChainLink("page_number"));
                   foreach ($topicsList as $topicData) {
                       $topic   = new Topic($topicData['id']);
                       $newBody = $newsTemplate;

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic) {
                           return RouteAgent::buildRoute("user-avatar", $topic->getAuthorId());
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic) {
                           return ($topic->getStatus() == 0
                                   ? BuildManager::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic) {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic) {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildManager::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getCategory()->getName();
                       });

                       $result .= TagAgent::compileFromTemporaryContainer($newBody);
                   }

                   return $result;
               });
           }
           case "main-page":
           {
               return TagAgent::useTemporaryContainer(function () use ($currentRoute) {
                   $result = '';

                   $newsTemplate = BuildManager::includeFromTemplateAndCompile("news/preview.html");
                   if (!TagAgent::temporaryContainerFilled()) {
                       $topicAuthorAvatarTag     = Tag::create("{TOPIC_AUTHOR_AVATAR}");
                       $topicAuthorIdTag         = Tag::create("{TOPIC_AUTHOR_ID}");
                       $topicAuthorNicknameTag   = Tag::create("{TOPIC_AUTHOR_NICKNAME}");
                       $topicAuthorGroupColorTag = Tag::create("{TOPIC_AUTHOR_GROUP_COLOR}");
                       $topicAuthorGroupNameTag  = Tag::create("{TOPIC_AUTHOR_GROUP_NAME}");
                       $topicAuthorGroupIdTag    = Tag::create("{TOPIC_AUTHOR_GROUP_ID}");
                       $topicLikesCountTag       = Tag::create("{TOPIC_LIKES_COUNT}");
                       $topicDislikesCountTag    = Tag::create("{TOPIC_DISLIKES_COUNT}");
                       $topicMarksCountTag       = Tag::create("{TOPIC_MARKS_COUNT}");
                       $topicNameTag             = Tag::create("{TOPIC_NAME}");
                       $topicBodyTag             = Tag::create("{TOPIC_BODY}");
                       $topicStatusIconTag       = Tag::create("{TOPIC_STATUS_ICON}");
                       $topicCategoryTag         = Tag::create("{TOPIC_CATEGORY}");
                       $topicIdTag               = Tag::create("{TOPIC_ID}");
                   }

                   $topicsList = ForumAgent::GetTopicList();
                   foreach ($topicsList as $topicData) {
                       $topic   = new Topic($topicData['id']);
                       $newBody = $newsTemplate;

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic) {
                           return RouteAgent::buildRoute("user-avatar", $topic->getAuthorId());
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic) {
                           return ($topic->getStatus() == 0
                                   ? BuildManager::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic) {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic) {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildManager::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getCategory()->getName();
                       });

                       $result .= TagAgent::compileFromTemporaryContainer($newBody);
                   }

                   return $result;
               });
           }
           case "category-topics":
           {
               return TagAgent::useTemporaryContainer(function () use ($currentRoute) {
                   $result = '';

                   $topicsList = ForumAgent::GetTopicList(1, false, $currentRoute->getValueOfChainLink("category_id"));
                   if (empty($topicsList)) {
                       //@todo: сделать красивую страничку для выывода
                       return LanguageManager::GetTranslation("newsviewer.empty_category");
                   }

                   $newsTemplate = BuildManager::includeFromTemplateAndCompile("news/preview.html");
                   if (!TagAgent::temporaryContainerFilled()) {
                       $topicAuthorAvatarTag     = Tag::create("{TOPIC_AUTHOR_AVATAR}");
                       $topicAuthorIdTag         = Tag::create("{TOPIC_AUTHOR_ID}");
                       $topicAuthorNicknameTag   = Tag::create("{TOPIC_AUTHOR_NICKNAME}");
                       $topicAuthorGroupColorTag = Tag::create("{TOPIC_AUTHOR_GROUP_COLOR}");
                       $topicAuthorGroupNameTag  = Tag::create("{TOPIC_AUTHOR_GROUP_NAME}");
                       $topicAuthorGroupIdTag    = Tag::create("{TOPIC_AUTHOR_GROUP_ID}");
                       $topicLikesCountTag       = Tag::create("{TOPIC_LIKES_COUNT}");
                       $topicDislikesCountTag    = Tag::create("{TOPIC_DISLIKES_COUNT}");
                       $topicMarksCountTag       = Tag::create("{TOPIC_MARKS_COUNT}");
                       $topicNameTag             = Tag::create("{TOPIC_NAME}");
                       $topicBodyTag             = Tag::create("{TOPIC_BODY}");
                       $topicStatusIconTag       = Tag::create("{TOPIC_STATUS_ICON}");
                       $topicCategoryTag         = Tag::create("{TOPIC_CATEGORY}");
                       $topicIdTag               = Tag::create("{TOPIC_ID}");
                   }
                   foreach ($topicsList as $topicData) {
                       $topic   = new Topic($topicData['id']);
                       $newBody = $newsTemplate;

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic) {
                           return RouteAgent::buildRoute("user-avatar", $topic->getAuthorId());
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic) {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic) {
                           return ($topic->getStatus() == 0
                                   ? BuildManager::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic) {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic) {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildManager::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic) {
                           return $topic->getCategory()->getName();
                       });

                       $result .= TagAgent::compileFromTemporaryContainer($newBody);
                   }

                   return $result;
               });
           }
           case "rules-page":
           {
               return BuildManager::includeContent("engine/config/rules.sfc");
           }
           default:
           {
               return '';
           }
       }
   });
Tag::create("{ENGINE_META:KEYWORDS}")->setProcessingFunction(function () {
    $currentRoute = RouteAgent::getCurrentRoute();
    switch ($currentRoute->getName()) {
        case "static-page":
            return StaticPagesAgent::GetPageKeyWords($currentRoute->getValueOfChainLink('id'));
        default:
            return '';
    }
});
Tag::create("{PROFILE_REPUTATIONER:STYLESHEET}")->setProcessingFunction(function () {
    if (UserAgent::isAuthorized() || RouteAgent::getCurrentRoute()->getName('')) {
        return '<link href="{>|css-main:reputationer-style.css}" rel="stylesheet">';
    }
    else {
        return "";
    }
});
Tag::create("{PROFILE_UPLOADER:STYLESHEET}")
   ->setProcessingFunction(function () {
       return !defined("TT_UPLOADER") ? '' : '<link rel="stylesheet" href="{>|template-uploader-css:uploader-style.css}">';
   });
Tag::create("{PROFILE_UPLOADER:JS}")
   ->setProcessingFunction(function () {
       return !defined("TT_UPLOADER") ? '' : '<script src="{>|template-uploader-js:uploaderscript.js}" type="application/javascript">';
   });
Tag::create("{PROFILE_UPLOADER_BLOCK}")
   ->setProcessingFunction(function () {
       return !defined("TT_UPLOADER") ? '' : PluginManager::IntegrateFooterJS();
   });
Tag::create("{PROFILE_PAGE_SEE_ERRORS}")
   ->setProcessingFunction(function () {
       return BuildManager::includeFromTemplateAndCompile("profile/autherrors.html");
   });
Tag::create("{PROFILE_PAGE:HEADER}")
   ->setProcessingFunction(function () {
       return BuildManager::includeFromTemplateAndCompile("profile/header.html");
   });
Tag::create("{PROFILE_PAGE:PAGE_NAME}")
   ->setProcessingFunction(function () {
       if (UserAgent::isAuthorized()) {
           return LanguageManager::GetTranslation("profile");
       }
       else {
           return LanguageManager::GetTranslation("authorization");
       }
   });
Tag::create("{PROFILE_MAIN_BODY}")
   ->setProcessingFunction(function () {
       if (UserAgent::isAuthorized()) {
           return 123;
       }
       else {
           return BuildManager::includeFromTemplateAndCompile("profile/auth.html");
       }
   });
Tag::create("{AUTH_PAGE:UID_INPUT_PLACEHOLDER}")
   ->setProcessingFunction(function () {
       if (Engine::GetEngineInfo("account.need_activate")) {
           return LanguageManager::GetTranslation("email");
       }
       else {
           return LanguageManager::GetTranslation("login");
       }
   });
Tag::create("{PROFILE_PAGE_FOOTER}")
   ->setProcessingFunction(function () {
       return BuildManager::includeFromTemplateAndCompile("profile/footer.html");
   });
Tag::create("{AUTH_PAGE:SIGN_UP}")
   ->setProcessingFunction(function () {
       return BuildManager::includeFromTemplateAndCompile("profile/authsignup.html");
   });
Tag::create("{AUTH_REMAINDER}")
   ->setProcessingFunction(function () {
       return BuildManager::includeFromTemplateAndCompile("profile/authremaindpass.html");
   });
Tag::create("{PROFILE_PAGE_GUI_SCRIPT}")
   ->setProcessingFunction(function () {
       return '<script src="{>|js-profile:userscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_JS:SHOW_PANEL}")
   ->setProcessingFunction(function () {
       return '<script src="{>|js-profile:authscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_PAGE_GUI_CUSTOM_SCRIPT}")
   ->setProcessingFunction(function () {
       return '<script src="{>|js-profile:customscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_REPUTATIONER:JS}")
   ->setProcessingFunction(function () {
       return '<script src="{>|js-profile:reputationerscript.js}" type="application/javascript"></script>';
   });