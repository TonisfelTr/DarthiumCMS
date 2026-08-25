<?php

use Builder\Controllers\BannerAgent;
use Builder\Controllers\BuildAgent;
use Builder\Controllers\NavbarAgent;
use Builder\Controllers\SidePanelsAgent;
use Builder\Controllers\TagAgent;
use Builder\Models\SidePanel;
use Builder\Services\Tag;
use Engine\Engine;
use Engine\LanguageAgent;
use Engine\PluginAgent;
use Engine\RouteAgent;
use Exceptions\TavernException;
use Forum\ForumAgent;
use Forum\Models\Topic;
use Forum\StaticPagesAgent;
use Users\Models\Group;
use Users\Models\User;
use Users\Services\FlashSession;
use Users\UserAgent;

$sidePanels   = SidePanelsAgent::GetPanelsList();
$navbarEndUls = "";

if (UserAgent::isAuthorized() && RouteAgent::getCurrentRoute()->getName() != "profile-page-defined") {
    $user = UserAgent::getCurrentUser();
} elseif (RouteAgent::getCurrentRoute()->getName() == "profile-page-defined") {
    $user = UserAgent::getUser(RouteAgent::getCurrentRoute()->getValueOfChainLink("id"));
} else {
    $user = false;
}

if ($user !== false) {
    $additionalFields         = UserAgent::GetAdditionalFieldsList();
    $additionalFieldsContents = UserAgent::GetAdditionalFieldsListOfUser($user->getId());
} else {
    $additionalFields         = null;
    $additionalFieldsContents = null;
}

Tag::create("{INDEX_PAGE_NAVBAR}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("navbar.html");
});
Tag::create("{INDEX_NAVBAR_BTNS}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("nav-btn.html");
});
Tag::create("{END_NAVBAR_BTNS}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("nav-btn-end.html");
});
Tag::create("{INDEX_PROFILE_MENU}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile((UserAgent::isAuthorized() ? "pam_auth" : "pam_unauth")
                                                     . ".html");
});
Tag::create("{INDEX_PROFILE_MENU:MAIL_SPAN_CLASS}")->setProcessingFunction(function () use ($user): string {
    return $user === false ? "" : "nav-btn-"
                                  . (($user->MessageManager()->getNotReadCount() > 0) ? "new-" : "")
                                  . "counter";
});
Tag::create("{INDEX_PROFILE_MENU:MAIL_SPAN_COUNT}")->setProcessingFunction(function () use ($user): string {
    return $user === false
        ? ""
        : (($user->MessageManager()->getNotReadCount() > 10)
            ? "10+"
            : "{$user->MessageManager()->getNotReadCount()}");
});
Tag::create("{INDEX_PROFILE_MENU:NOTIF_SPAN_CLASS}")->setProcessingFunction(function () use ($user): string {
    return $user === false ? "" : "nav-btn-" . (($user->Notifications()->getNotificationsUnreadCount()) ? "new-"
            : "") . "counter";
});
Tag::create("{INDEX_PROFILE_MENU:NOTIF_SPAN_COUNT}")->setProcessingFunction(function () use ($user): string {
    return $user === false
        ? ""
        : (($user->Notifications()->getNotificationsUnreadCount() > 10) ? "10+"
            : "{$user->Notifications()->getNotificationsUnreadCount()}");
});
Tag::create("{INDEX_PROFILE_MENU:USER_NICKNAME}")->setProcessingFunction(function () use ($user): string {
    return $user ? $user->getNickname() : "{%|profile}";
});
Tag::create("{INDEX_PROFILE_MENU:ADMPANEL_BUTTON}")->setProcessingFunction(function () use ($user): string {
    return $user && $user->getUserGroup()->getPermission("enterpanel")
        ? '<li><a href="{>|adminpanel-page}">{%|adminpanel-link}</a>'
        : '';
});
Tag::create("{INDEX_CATEGORY_LIST}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("category_list.html");
});
Tag::create("{INDEX_PAGE_HEADER}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("header.html");
});
Tag::create("{INDEX_PAGE_FOOTER}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("footer.html");
});
Tag::create("{CURRENT_YEAR}")->setProcessingFunction(function (): string {
    return date("Y");
});
Tag::create("{INDEX_PAGE_LEFT}")->setProcessingFunction(function () use ($sidePanels): string {
    $sidePanelHTML
                = BuildAgent::includeContent("leftside.html", TEMPLATE_ROOT);
    $resultHTML = "";
    foreach ($sidePanels as $sidePanel) {
        $panel = new SidePanel($sidePanel["id"]);
        if ($panel->getVisibility()) {
            if ($panel->getType() == "leftside") {
                $leftPanel  = BuildAgent::replaceOnceInString("{PANEL_TITLE}", $panel->getName(), $sidePanelHTML);
                $leftPanel  = BuildAgent::replaceOnceInString("{PANEL_CONTENT}", $panel->getContent(), $leftPanel);
                $resultHTML .= $leftPanel;
            }
        }
    }

    return $resultHTML;
});
Tag::create("{INDEX_PAGE_RIGHT}")->setProcessingFunction(function () use ($sidePanels): string {
    $sidePanelHTML
                = BuildAgent::includeContent("rightside.html", TEMPLATE_ROOT);
    $resultHTML = "";
    foreach ($sidePanels as $sidePanel) {
        $panel = new SidePanel($sidePanel["id"]);
        if ($panel->getVisibility()) {
            if ($panel->getType() == "rightside") {
                $rightPanel = BuildAgent::replaceOnceInString("{PANEL_TITLE}", $panel->getName(), $sidePanelHTML);
                $rightPanel = BuildAgent::replaceOnceInString("{PANEL_CONTENT}", $panel->getContent(), $rightPanel);
                $resultHTML .= $rightPanel;
            }
        }
    }

    return $resultHTML;
});
Tag::create("{LAST_SITE_TOPICS}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("last_topic_list.html");
});
Tag::create("{STATISTIC_NOW_AVAILABLE}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile("online_list.html");
});
Tag::create("{MAIN_PAGE:FIRST_BIG_BANNER}")->setProcessingFunction(function (): string {
    $bigBanners      = BannerAgent::GetBanners("banner");
    $bigBannersCount = count($bigBanners);
    if ($bigBannersCount > 0) {
        $bigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
        $bigBanner = '<img class="img-bgbanner" src="{>|banners:' . $bigBanner . '}">';
    } else {
        $bigBannerUrl = RouteAgent::buildRoute("banners", "bigbanner.png");
        $bigBanner    = '<img class="img-bgbanner" src="' . $bigBannerUrl . '" title="{%|ad_is_free}">';
    }
    return $bigBanner;
});
Tag::create("{MAIN_PAGE:SECOND_BIG_BANNER}")->setProcessingFunction(function (): string {
    $bigBanners      = BannerAgent::GetBanners("banner");
    $bigBannersCount = count($bigBanners);
    if ($bigBannersCount > 0) {
        $bigBanner = $bigBanners[rand(0, $bigBannersCount - 1)]["content"];
        $bigBanner = '<img class="img-bgbanner" src="{>|banners:' . $bigBanner . '}">';
    } else {
        $bigBannerUrl = RouteAgent::buildRoute("banners", "bigbanner.png");
        $bigBanner    = '<img class="img-bgbanner" src="' . $bigBannerUrl . '" title="{%|ad_is_free}">';
    }
    return $bigBanner;
});
Tag::create("{MAIN_PAGE:FOOTER_FIRST_SMALL_BANNER}")->setProcessingFunction(function (): string {
    $firstBanner = BannerAgent::GetBannersByName("firstbanner");
    if (empty($firstBanner)) {
        $firstBanner = '<img class="img-smbanner" src="{>|banners:smallbanner.png}" title="{%|ad_is_free}">';
    } else {
        $firstBanner = '<div class="img-smbanner">' . $firstBanner['content'] . '</div>';
    }

    return $firstBanner;
});
Tag::create("{MAIN_PAGE:FOOTER_SECOND_SMALL_BANNER}")->setProcessingFunction(function (): string {
    $secondBanner = BannerAgent::GetBannersByName("secondbanner");
    if (empty($secondBanner)) {
        $secondBanner = '<img class="img-smbanner" src="{>|banners:smallbanner.png}" title="{%|ad_is_free}">';
    } else {
        $secondBanner = "<div class=\"img-smbanner\">" . $secondBanner['content'] . "</div>";
    }

    return $secondBanner;
});
Tag::create("{INDEX_CATEGORY_HINT}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile('category_hint.html');
});
Tag::create("{INDEX_MESSAGE_BOX}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile('messagebox.html');
});
Tag::create("{INDEX_SEARCHING}")->setProcessingFunction(function (): string {
    return BuildAgent::includeFromTemplateAndCompile('searchpanel.html');
});
Tag::create("{INDEX_SEARCHING_TEXT}")->setProcessingFunction(function (): string {
    if (in_array(RouteAgent::getCurrentRoute()
                           ->getName(), ['topic-searching-by-name', 'topic-searching-by-author', 'topic-searching-by-quiz'])) {
        return RouteAgent::getCurrentRoute()->getParameter('s', true);
    } else {
        return '';
    }
});
Tag::create("{INDEX_SEARCHING_TYPE}")->setProcessingFunction(function (): string {
    if (RouteAgent::getCurrentRoute()->getName() == 'topic-searching-by-author') {
        return '$("button#search-by-author").click()';
    } elseif (RouteAgent::getCurrentRoute()->getName() == 'topic-searching-by-quiz') {
        return '$("button#search-by-quiz").click()';
    }

    return '';
});
Tag::create("{INDEX_PAGE_MAIN_BLOCK}", true)
   ->setProcessingFunction(function (): string {
       $currentRoute = RouteAgent::getCurrentRoute();
       switch ($currentRoute->getName()) {
           case "new-topic":
               return BuildAgent::includeContent("site/newtopic.php");
           case "special-page":
               $specialPageId = $currentRoute->getValueOfChainLink("id");
               return Engine::CompileBBCode(file_get_contents(HOME_ROOT
                                                              . "site/statics/"
                                                              . $specialPageId
                                                              . ".txt", FILE_USE_INCLUDE_PATH));
           case "topic-view":
               return BuildAgent::includeFromTemplate("news/new.html");
           case "topic-searching-by-name":
               return BuildAgent::includeContent("site/search.php");
           case "group-list":
           {
               return BuildAgent::includeFromTemplateAndCompile("grouptable.html");
           }
           case "plugin-page":
               return BuildAgent::includeFromPlugin($currentRoute->getValueOfChainLink("plugin_page"), "bin/engine.php");
           case "main-page-paginated":
           {
               return TagAgent::useTemporaryContainer(function () use ($currentRoute) {
                   $result = '';

                   $newsTemplate = BuildAgent::includeFromTemplateAndCompile("news/preview.html");
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

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic): string {
                           return RouteAgent::getPublicUrl("uploads/avatars/{$topic->getAuthor()->getAvatar()}");
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic): string {
                           return ($topic->getStatus() == 0
                                   ? BuildAgent::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic): string {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic): string {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildAgent::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic): string {
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

                   $newsTemplate = BuildAgent::includeFromTemplateAndCompile("news/preview.html");
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

                   $topicsList = ForumAgent::getTopicList();
                   foreach ($topicsList as $topicData) {
                       $topic   = new Topic($topicData['id']);
                       $newBody = $newsTemplate;

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic): string {
                           return RouteAgent::getPublicUrl("uploads/avatars/{$topic->getAuthor()->getAvatar()}");
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic): string {
                           return ($topic->getStatus() == 0
                                   ? BuildAgent::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic): string {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic): string {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildAgent::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic): string {
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
                       return LanguageAgent::getTranslation("newsviewer.empty_category");
                   }

                   $newsTemplate = BuildAgent::includeFromTemplateAndCompile("news/preview.html");
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

                       $topicAuthorAvatarTag->setProcessingFunction(function () use ($topic): string {
                           return RouteAgent::getPublicUrl("uploads/avatars/{$topic->getAuthor()->getAvatar()}");
                       });
                       $topicAuthorIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getId();
                       });
                       $topicIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getId();
                       });
                       $topicAuthorNicknameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getNickname();
                       });
                       $topicAuthorGroupNameTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getName();
                       });
                       $topicAuthorGroupColorTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getColor();
                       });
                       $topicAuthorGroupIdTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getAuthor()->getUserGroup()->getId();
                       });
                       $topicLikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getLikes();
                       });
                       $topicDislikesCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getDislikes();
                       });
                       $topicMarksCountTag->setProcessingFunction(function () use ($topic): string {
                           return (string)$topic->getMarksCount();
                       });
                       $topicNameTag->setProcessingFunction(function () use ($topic): string {
                           return ($topic->getStatus() == 0
                                   ? BuildAgent::includeFromTemplateAndCompile('news/lockicon.html')
                                   : "") . htmlentities($topic->getName());
                       });
                       $topicBodyTag->setProcessingFunction(function () use ($topic): string {
                           return Engine::ChatFilter(
                               Engine::CompileMentions(
                                   html_entity_decode(
                                       Engine::CompileBBCode($topic->getPretext())
                                   )
                               )
                           );
                       });
                       $topicStatusIconTag->setProcessingFunction(function () use ($topic): string {
                           return ForumAgent::IsExistQuizeInTopic($topic->getId())
                               ? BuildAgent::includeFromTemplateAndCompile('news/quizicon.html')
                               : "";
                       });
                       $topicCategoryTag->setProcessingFunction(function () use ($topic): string {
                           return $topic->getCategory()->getName();
                       });

                       $result .= TagAgent::compileFromTemporaryContainer($newBody);
                   }

                   return $result;
               });
           }
           case "rules-page":
           {
               return BuildAgent::includeContent("engine/config/rules.sfc");
           }
           default:
           {
               return '';
           }
       }
   });
Tag::create("{ENGINE_META:KEYWORDS}")->setProcessingFunction(function (): string {
    $currentRoute = RouteAgent::getCurrentRoute();
    switch ($currentRoute->getName()) {
        case "static-page":
            return StaticPagesAgent::GetPageKeyWords($currentRoute->getValueOfChainLink('id'));
        default:
            return '';
    }
});
Tag::create("{PROFILE_REPUTATIONER:STYLESHEET}")->setProcessingFunction(function (): string {
    if (UserAgent::isAuthorized() || RouteAgent::getCurrentRoute()->getName('')) {
        return '<link href="{assets|css/reputationer-style.css}" rel="stylesheet">';
    } else {
        return "";
    }
});
Tag::create("{PROFILE_UPLOADER:STYLESHEET}")
   ->setProcessingFunction(function (): string {
       return !defined("TT_UPLOADER") ? '' : '<link rel="stylesheet" href="{assets|css/uploader-style.css}">';
   });
Tag::create("{PROFILE_UPLOADER:JS}")
   ->setProcessingFunction(function (): string {
       return !defined("TT_UPLOADER") ? '' : '<script src="{assets|js/uploaderscript.js}" type="application/javascript">';
   });
Tag::create("{PROFILE_UPLOADER_BLOCK}")
   ->setProcessingFunction(function (): string {
       return !defined("TT_UPLOADER") ? '' : PluginAgent::IntegrateFooterJS();
   });
Tag::create("{PROFILE_PAGE_SEE_ERRORS}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/autherrors.html");
   });
Tag::create("{PROFILE_PAGE:HEADER}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/header.html");
   });
Tag::create("{PROFILE_PAGE:PAGE_NAME}")
   ->setProcessingFunction(function (): string {
       if (UserAgent::isAuthorized()) {
           return LanguageAgent::getTranslation("profile");
       } else {
           return LanguageAgent::getTranslation("authorization");
       }
   });
Tag::create("{PROFILE_PAGE:RULES_BLOCK}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/auth_rules.html");
   });
Tag::create("{PROFILE_PAGE:REGISTRATION_BLOCK}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/auth_registration.html");
   });
Tag::create("{PROFILE_PAGE:RESTORE_BLOCK}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/auth_remaindpass.html");
   });
Tag::create("{PROFILE_MAIN_BODY}")
    ->setProcessingFunction(function () use ($user, $additionalFields, $additionalFieldsContents): string {
        $isAuthorized = UserAgent::isAuthorized();
        $isNotCurrentAuthorizedUser = RouteAgent::getCurrentRoute()->getValueOfChainLink("id") != "null";

       if ($user != false) {
           // @todo: тут должна быть проверка на просмотр профилей будучи неавторизованным, либо
           // если просмотр чужих профилей выключен настройками системы.
           if ($isNotCurrentAuthorizedUser) {
               Tag::create("{PROFILE_PAGE_EDIT}", true, false)
                  ->setProcessingFunction(function (): string {
                      return "";
                  });
               Tag::create("{PROFILE_PAGE_PM}", true, false)
                  ->setProcessingFunction(function (): string {
                      return "";
                  });
               Tag::create("{PROFILE_PAGE_NOTIFICS}", true, false)
                  ->setProcessingFunction(function (): string {
                      return "";
                  });
               Tag::create("{PROFILE_PAGE_FRIENDS}", true, false)
                  ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                      if ($isAuthorized && !$isNotCurrentAuthorizedUser) {
                          return BuildAgent::includeFromTemplateAndCompile("profile/user_friends.html");
                      } else {
                          return "";
                      }
                  });
               Tag::create("{PROFILE_FOOTER_BTNS}", true, false)
                  ->setProcessingFunction(function () use ($isAuthorized): string {
                      if ($isAuthorized) {
                          return BuildAgent::includeFromTemplateAndCompile("profile/user_authorized_foreign_buttons.html", true);
                      } else {
                          return BuildAgent::includeFromTemplateAndCompile("profile/user_not_authorized_buttons.html", true);
                      }
                  });
           } elseif ($isAuthorized && UserAgent::getCurrentUser()->getId() == $user->getId()) {
               Tag::create("{PROFILE_PAGE_EDIT}", true, false)
                  ->setProcessingFunction(function (): string {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_edit.html", true);
                  });
               Tag::create("{PROFILE_PAGE_PM}", true, false)
                  ->setProcessingFunction(function (): string {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_pm.html", true);
                  });
               Tag::create("{PROFILE_PAGE_NOTIFICS}", true, false)
                  ->setProcessingFunction(function (): string {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_notifications.html", true);
                  });
               Tag::create("{PROFILE_PAGE_FRIENDS}", true, false)
                  ->setProcessingFunction(function (): string {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_friends.html", true);
                  });
               Tag::create("{PROFILE_FOOTER_BTNS}", true, false)
                  ->setProcessingFunction(function (): string {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_authorized_self_buttons.html", true);
                  });
           }

           Tag::create("{PROFILE_PAGE:USER_REGDATETIME}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return date("d.m.Y", strtotime($user->getRegDate()));
              });
           Tag::create("{PROFILE_PAGE:USER_FROM}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getFrom() ?? LanguageAgent::getTranslation("not_set");;
              });
           Tag::create("{PROFILE_PAGE:USER_REALNAME}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getRealName() ?? LanguageAgent::getTranslation("not_set");
              });
           Tag::create("{PROFILE_PAGE:USER_BIRTHDAY}")
              ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                  if ($user === false) {
                      return '';
                  }

                  $isPublic = $user->isBirthdayPublic();

                  if ($isNotCurrentAuthorizedUser) {
                      if ($isPublic) {
                          if ($user->getBirth() == "") {
                              return LanguageAgent::getTranslation("birthday")
                                     . ": "
                                     . LanguageAgent::getTranslation("not_set");
                          } else {
                              return LanguageAgent::getTranslation("birthday")
                                     . ": "
                                     . $user->getBirth();
                          }
                      } else {
                          return "";
                      }
                  } elseif ($isAuthorized) {
                      if ($user->getBirth() == "") {
                          return LanguageAgent::getTranslation("birthday")
                                 . ": "
                                 . LanguageAgent::getTranslation("not_set");
                      } else {
                          return LanguageAgent::getTranslation("birthday")
                                 . ": "
                                 . $user->getBirth();
                      }
                  }
              });
           Tag::create("{PROFILE_PAGE:USER_SEX}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  switch ($user->getSex()) {
                      case 1:
                          return LanguageAgent::getTranslation("not_set");
                      case 2:
                          return LanguageAgent::getTranslation("gender_male");
                      case 3:
                          return LanguageAgent::getTranslation("gender_female");
                  }
              });
           Tag::create("{PROFILE_PAGE:CUSTOM_ADDITIONALS}")
              ->setProcessingFunction(function () use ($user, $additionalFields, $additionalFieldsContents): string {
                  if ($user === false) {
                      return '';
                  }

                  $fields = "";

                  foreach ($additionalFields as $fieldId => $field) {
                      $fields .= $additionalFields[$fieldId]["name"] . ": " . $field["content"];
                  }

                  return $fields;
              });
           Tag::create("{PROFILE_PAGE:USER_REFERER}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  Tag::create("{PROFILE_REFERRER_USER_ID}")
                     ->setProcessingFunction(function () use ($user): string {
                         return $user->getReferrer()->getId();
                     });

                  return $user->getReferrer() != null
                      ? LanguageAgent::getTranslation("referrer") . ": " . $user->getReferrer()->getNickname()
                      : "";
              });
           Tag::create("{PROFILE_PAGE:USER_REP_POINTS}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getReputation()->getReputationPoints()
                         . " "
                         . LanguageAgent::getTranslation("point(s)");
              });
           Tag::create("{PROFILE_PAGE:USER_EMAIL}")
              ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                  if ($user === false) {
                      return '';
                  }

                  if ($isNotCurrentAuthorizedUser) {
                      if ($user->isEmailPublic()) {
                          return "Email: " . $user->getEmail() . "<br>";
                      } else {
                          return "";
                      }
                  } elseif ($isAuthorized) {
                      return "Email: " . $user->getEmail() . "<br>";
                  } else {
                      return "";
                  }
              });
           Tag::create("{PROFILE_PAGE:USER_SKYPE_LINK}", true, false)
              ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                  if ($user === false) {
                      return '';
                  }

                  $skype = $user->getSkype();

                  if ($isNotCurrentAuthorizedUser) {
                      if ($user->isSkypePublic()) {
                          return "Skype: "
                                 . ($skype != "" ? $skype : LanguageAgent::getTranslation("not_set"))
                                 . "<br>";
                      } else {
                          return "";
                      }
                  } elseif ($isAuthorized) {
                      return "Skype: " . ($skype != "" ? $skype : LanguageAgent::getTranslation("not_set")) . "<br>";
                  } else {
                      return "";
                  }
              });
           Tag::create("{PROFILE_PAGE:USER_VK_LINK}", true, false)
              ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                  if ($user === false) {
                      return '';
                  }

                  $vk = $user->getVK();

                  if ($isNotCurrentAuthorizedUser) {
                      if ($user->isVKPublic()) {
                          return "VK: " . ($vk != "" ? $vk : LanguageAgent::getTranslation("not_set")) . "<br>";
                      } else {
                          return "";
                      }
                  } elseif ($isAuthorized) {
                      return "VK: " . ($vk != "" ? $vk : LanguageAgent::getTranslation("not_set")) . "<br>";
                  } else {
                      return "";
                  }
              });
           Tag::create("{PROFILE_PAGE:CONTACT_ADDITIONALS} ", true, false)
              ->setProcessingFunction(function () use ($user, $isAuthorized, $isNotCurrentAuthorizedUser): string {
                  if ($user === false) {
                      return '';
                  }

                  $contactFields     = UserAgent::GetAdditionalFieldsList();
                  $contactUserFields = UserAgent::GetAdditionalFieldsListOfUser($user->getId());

                  foreach ($contactFields as $fieldId => $field) {
                      if ($field["type"] != UserAgent::AT_CONTACT) {
                          continue;
                      }

                      if ($isNotCurrentAuthorizedUser) {
                          if ($contactUserFields[$fieldId]["isPublic"]) {
                              return $field["name"] . ": " . $contactUserFields[$fieldId]["content"] . "<br>";
                          } else {
                              return "";
                          }
                      } elseif ($isAuthorized) {
                          return $field["name"] . ": " . $contactUserFields[$fieldId]["content"] . "<br>";
                      } else {
                          return "";
                      }
                  }

                  return "";
              });
           Tag::create("{PROFILE_PAGE:USER_HOBBIES}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }
                  return $user->getHobbies() ?? LanguageAgent::getTranslation("not_set");
              });
           Tag::create("{PROFILE_PAGE:USER_ABOUT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }
                  return $user->getAbout() ?? LanguageAgent::getTranslation("not_set");
              });
           Tag::create("{PROFILE_PAGE:INFO_ADDITIONALS}")
              ->setProcessingFunction(function () use ($user, $isNotCurrentAuthorizedUser, $isAuthorized, $additionalFields, $additionalFieldsContents): string {
                  if ($user === false) {
                      return '';
                  }

                  $infoUserFields = $additionalFieldsContents;

                  foreach ($additionalFields as $fieldId => $field) {
                      if ($field["type"] != UserAgent::AT_INFO) {
                          continue;
                      }

                      if ($isNotCurrentAuthorizedUser) {
                          if ($infoUserFields[$fieldId]["isPublic"]) {
                              return $field["name"] . ": " . $infoUserFields[$fieldId]["content"] . PHP_EOL;
                          } else {
                              return "";
                          }
                      } elseif ($isAuthorized) {
                          return $field["name"] . ": " . $infoUserFields[$fieldId]["content"] . PHP_EOL;
                      } else {
                          return "";
                      }
                  }

                  return "";
              });
           Tag::create("{PROFILE_PAGE:USER_SIGNATURE}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getSignature() ?? LanguageAgent::getTranslation("not_set");
              });
           Tag::create("{PROFILE_PAGE:USER_REPORT_CREATED_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getReportsCreatedCount();
              });
           Tag::create("{PROFILE_PAGE:USER_TOPICS_CREATED_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getTopicsCreatedCount();
              });
           Tag::create("{PROFILE_PAGE:USER_COMMENTS_CREATED_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getCommentsCreatedCount();
              });
           Tag::create("{PROFILE_PAGE:USER_FRIENDS_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return $user->getFriendsCount();
              });
           Tag::create("{PROFILE_PAGE:USER_ONLINE_FRIENDS_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  return UserAgent::GetOnlineFriendsCount($user->getId());
              });
           Tag::create("{PROFILE_PAGE:USER_FRIENDLIST_MANAGE_BTN}")
              ->setProcessingFunction(function () use ($user, $isNotCurrentAuthorizedUser, $isAuthorized): string {
                  if ($user === false) {
                      return '';
                  }

                  if (!$isNotCurrentAuthorizedUser && $isAuthorized) {
                      return BuildAgent::includeFromTemplateAndCompile("profile/user_friends_manage_button.html");
                  } else {
                      return "";
                  }
              });
           Tag::create("{PROFILE_FRIENDS_TABLE_BODY}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  $result = '';
                  /** @var $friend User User as friend */
                  foreach ($user->FriendList()->convertToUserList() as $friend) {
                      $result .= TagAgent::useTemporaryContainer(function () use ($user, $friend) {
                          Tag::create("{FRIEND_AVATAR}")
                             ->setProcessingFunction(function () use ($friend): string {
                                 return $friend->getAvatar();
                             });
                          Tag::create("{FRIEND_NICKNAME}")
                             ->setProcessingFunction(function () use ($friend): string {
                                 return $friend->getNickname();
                             });
                          Tag::create("{FRIEND_ADD_DATETIME}")
                             ->setProcessingFunction(function () use ($user, $friend): string {
                                 return $user->FriendList()->getAddDate($friend->getId());
                             });

                          return BuildAgent::includeFromTemplateAndCompile("profile/user_friends_table.html");
                      });
                  }

                  return $result;
              });
           Tag::create("{PROFILE_PM_SPAN_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  $notReadCount = $user->MessageManager()->getNotReadCount();

                  return ($notReadCount > 10) ? "10+" : $notReadCount;
              });
           Tag::create("{PROFILE_PM_SPAN_CLASS}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  if ($user->MessageManager()->getNotReadCount() > 0) {
                      return "profile-btn-new-counter";
                  } else {
                      return "profile-btn-counter";
                  }
              });
           Tag::create("{PROFILE_NOTIFICS_SPAN_CLASS}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  if ($user->Notifications()->getNotificationsUnreadCount() > 0) {
                      return "profile-btn-new-counter";
                  } else {
                      return "profile-btn-counter";
                  }
              });
           Tag::create("{PROFILE_NOTIFICS_SPAN_COUNT}")
              ->setProcessingFunction(function () use ($user): string {
                  if ($user === false) {
                      return '';
                  }

                  $notReadNotificationsCount = $user->Notifications()->getNotificationsUnreadCount();

                  return $notReadNotificationsCount > 10 ? "10+" : $notReadNotificationsCount;
              });
           Tag::create("{PROFILE_PAGE_INFO}")
              ->setProcessingFunction(function (): string {
                  return BuildAgent::includeFromTemplateAndCompile("profile/user_info.html");
              });
           return BuildAgent::includeFromTemplateAndCompile("profile/user_profile.html");
       } else {
           return BuildAgent::includeFromTemplateAndCompile("profile/auth.html");
       }
   });
Tag::create("{AUTH_PAGE:UID_INPUT_PLACEHOLDER}")
   ->setProcessingFunction(function (): string {
       if (Engine::GetEngineInfo("account.need_activate")) {
           return LanguageAgent::getTranslation("email");
       } else {
           return LanguageAgent::getTranslation("login");
       }
   });
Tag::create("{PROFILE_PAGE_FOOTER}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/footer.html");
   });
Tag::create("{AUTH_PAGE:SIGN_UP}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/authsignup.html");
   });
Tag::create("{AUTH_PAGE:RULES}")
   ->setProcessingFunction(function (): string {
       return Engine::CompileBBCode(Engine::getRules());
   });
Tag::create("{AUTH_REMAINDER}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/authremaindpass.html");
   });
Tag::create("{PROFILE_PAGE_GUI_SCRIPT}")
   ->setProcessingFunction(function (): string {
       return '<script src="{assets|js/userscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_JS:SHOW_PANEL}")
   ->setProcessingFunction(function (): string {
       return '<script src="{assets|js/authscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_PAGE_GUI_CUSTOM_SCRIPT}")
   ->setProcessingFunction(function (): string {
       return '<script src="{assets|js/customscript.js}" type="application/javascript"></script>';
   });
Tag::create("{PROFILE_REPUTATIONER:JS}")
   ->setProcessingFunction(function (): string {
       return '<script src="{assets|js/reputationerscript.js}" type="application/javascript"></script>';
   });
Tag::create("{AUTH_PAGE:REGISTRATION_ERRORS}")
   ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/auth_reg_errors.html");
   });
Tag::create("{PROFILE_REPUTATIONER_BLOCK}")
   ->setProcessingFunction(function (): string {
       return TagAgent::useTemporaryContainer(function () {
           $block = BuildAgent::includeFromTemplateAndCompile("profile/user_reputationer_main.html");

           Tag::create("{PROFILE_REPUTATIONER:HIDDEN_ATTR}")
              ->setProcessingFunction(function (): string {
                  if (FlashSession::hasSystem("reputationer")) {
                      return "";
                  } else {
                      return "hidden";
                  }
              });

           return TagAgent::compileFromTemporaryContainer($block);
       });
   });
Tag::create("{PROFILE_PAGE:USER_NICKNAME}")
   ->setProcessingFunction(function () use ($user): string {
       if ($user !== false) {
           return $user->getNickname();
       }

       return '';
   });
Tag::create("{PROFILE_PAGE:USER_AVATAR}")
   ->setProcessingFunction(function (): string {
       $userId = RouteAgent::getCurrentRoute()->getValueOfChainLink("id");

       if ($userId != "null") {
           return RouteAgent::getPublicUrl("uploads/avatars/" . UserAgent::getUser($userId)->getAvatar());
       }

       if (UserAgent::getCurrentUser() != false) {
           return RouteAgent::getPublicUrl("uploads/avatars/" . UserAgent::getUser(UserAgent::getCurrentUser()->getId())->getAvatar());
       }

       return '';
   });
Tag::create("{PROFILE_PAGE:USER_GROUP_COLOR}")
    ->setProcessingFunction(function () use ($user): string {
        $color = $user->getUserGroup()->getColor();

       return $color == "#000000" ? "#fff" : $color;
   });
Tag::create("{PROFILE_PAGE:USER_GROUP_NAME}")
    ->setProcessingFunction(function () use ($user): string {
       if (UserAgent::getCurrentUser() === false) {
           return '';
       }

        return $user->getUserGroup()->getName();
   });
Tag::create("{PROFILE_PAGE:USER_LASTONLINE}")
    ->setProcessingFunction(function () use ($user): string {
        if ($user->getId() == UserAgent::getCurrentUser()->getId()) {
            return "<span style=\"color: #00dd00;\">" . LanguageAgent::getTranslation("online") . "</span>";
        }

        if ($user->getLastTime() <= 0) {
            if ($user->getSex() == 3)
                $lastOnline = LanguageAgent::getTranslation("not_signed_in_she");
            else
                $lastOnline = LanguageAgent::getTranslation("not_signed_in_he");
        } else {
            if (Engine::GetSiteTime() + 15 * 60 > $user->getLastTime() + 15 * 60) {
                if ($user->getSex() == 3) {
                    $lastOnline = LanguageAgent::getTranslation("signed_in_she");
                } else {
                    $lastOnline = LanguageAgent::getTranslation("signed_in_he");
                }

                $lastOnline .= " "
                                . LanguageAgent::getTranslation("in")
                                . " "
                                . Engine::DatetimeFormatToRead(date("Y-m-d H:i:s", $user->getLastTime()));
            } else {
                $lastOnline = "<span style=\"color: #00dd00;\">" . LanguageAgent::getTranslation("online") . "</span>";
            }
        }

        return $lastOnline;
   });
Tag::create("{PROFILE_ERRORS_INFO}")
    ->setProcessingFunction(function (): string {
       return BuildAgent::includeFromTemplateAndCompile("profile/user_profile_errors.html", true);
   });
Tag::create("{PROFILE_PAGE_EDIT}")
    ->setProcessingFunction(function () use ($user, $additionalFields, $additionalFieldsContents): string {
        if ($user === false || $user->getId() != UserAgent::getCurrentUser()->getId()) {
           return '';
       }

       $template = BuildAgent::includeFromTemplateAndCompile("profile/user_edit.html", true);

       $template       = str_replace("{PROFILE_PAGE:USER_FROM_TEXT}", htmlentities($user->getFrom()), $template);
       $template
                       = str_replace("{PROFILE_PAGE:USER_REALNAME_TEXT}", htmlentities($user->getRealName()), $template);
       $template       = str_replace("{PROFILE_PAGE:USER_BIRTHDAY_TEXT}", htmlentities($user->getBirth()), $template);
       $userSex        = $user->getSex();
       $isNon          = $userSex == 0 ? "selected" : "";
       $isFemale       = $userSex == 1 ? "selected" : "";
       $isMale         = $userSex == 2 ? "selected" : "";
       $userSexOptions = "<option value='0' $isNon>" . LanguageAgent::getTranslation("not_set") . "</option>";
       $userSexOptions .= "<option value='2' $isMale>" . LanguageAgent::getTranslation("gender_male") . "</option>";
       $userSexOptions .= "<option value='1' $isFemale>"
                          . LanguageAgent::getTranslation("gender_female")
                          . "</option>";
       $template       = str_replace("{PROFILE_PAGE:USER_SEX_SELECTOR}", $userSexOptions, $template);
       $template       = str_replace("{PROFILE_PAGE:USER_VK}", $user->getVK(), $template);
       $template       = str_replace("{PROFILE_PAGE:USER_SKYPE}", $user->getSkype(), $template);

       $infoAdditionalFields    = "";
       $contactAdditionalFields = "";
       $additionalFieldTemplate = BuildAgent::includeFromTemplate("profile/user_edit_additional.html");
       foreach ($additionalFields as $fieldId => $field) {
           switch ($field["type"]) {
               case UserAgent::AT_INFO:
                   $infoAdditionalFields .= TagAgent::useTemporaryContainer(function () use ($additionalFieldTemplate, $fieldId, $field, $additionalFieldsContents) {
                       $result = $additionalFieldTemplate;

                       $result = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:ID}", $fieldId, $result);
                       $result = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:NAME}", $field["name"], $result);
                       $result
                               = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:VALUE}", $additionalFieldsContents[$fieldId]["content"], $result);

                       return $result;
                   });
                   break;
               case UserAgent::AT_CONTACT:
                   $contactAdditionalFields .= TagAgent::useTemporaryContainer(function () use ($additionalFieldTemplate, $fieldId, $field, $additionalFieldsContents) {
                       $result = $additionalFieldTemplate;

                       $result = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:ID}", $fieldId, $result);
                       $result = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:NAME}", $field["name"], $result);
                       $result
                               = str_replace("{PROFILE_USER_ADDITIONAL_FIELD:VALUE}", $additionalFieldsContents[$fieldId]["content"], $result);

                       return $result;
                   });
                   break;
               default:
                   break;
           }
       }

       $template = str_replace("{PROFILE_PAGE:CONTACT_ADDITIONALS_EDIT}", $contactAdditionalFields, $template);
       $template = str_replace("{PROFILE_PAGE:INFO_ADDITIONALS_EDIT}", $infoAdditionalFields, $template);
       $template = str_replace("{PROFILE_PAGE:USER_ABOUT_TEXT}", $user->getAbout(), $template);
       $template = str_replace("{PROFILE_PAGE:USER_HOBBIES_TEXT}", $user->getHobbies(), $template);
       $template = str_replace("{PROFILE_PAGE:USER_SIGNATURE_FORM}", $user->getSignature(), $template);
       $template = str_replace("{PROFILE_PAGE:USER_AVATAR_SIZE}", Engine::GetEngineInfo("avatar.width")
                                                                  . "x"
                                                                  . "avatar.height", $template);
       $template = str_replace("{PROFILE_PAGE:INFO_ADDITIONALS_EDIT}", $infoAdditionalFields, $template);
       $template = str_replace("{PROFILE_PAGE:PRIVACY_STATUS}", $user->isAccountPublic()
           ? "checked"
           : "", $template);
       $template = str_replace("{PROFILE_PAGE:USER_PRIVATE_VK}", $user->isVKPublic()
           ? "checked"
           : "", $template);
       $template = str_replace("{PROFILE_PAGE:USER_PRIVATE_SKYPE}", $user->isSkypePublic()
           ? "checked"
           : "", $template);
       $template = str_replace("{PROFILE_PAGE:USER_PRIVATE_EMAIL}", $user->isEmailPublic()
           ? "checked"
           : "", $template);
       $template = str_replace("{PROFILE_PAGE:USER_PRIVATE_BIRTHDAY}", $user->isBirthdayPublic()
           ? "checked"
           : "", $template);


       return $template;
   });
Tag::create("{PROFILE_PAGE_PM}")
    ->setProcessingFunction(function () use ($user): string {
        if ($user->getId() != UserAgent::getCurrentUser()->getId()) {
            return '';
        }

        Tag::create("{PROFILE_PAGE:USER_READ_PM_BUTTON}")
           ->setProcessingFunction(function () use ($user): string {
               if (RouteAgent::getCurrentRoute()->getName() != "profile-page-pm") {
                   return '';
               }

               return "<button class=\"profile-profile-panel-btn active\"
                                id=\"profile-pm-read-btn\"
                                type=\"button\"
                                onclick=\"showDescPanel('pm', 'read');\"><span class=\"glyphicons glyphicons-message-out\"></span> " .
                      \Engine\LanguageAgent::GetTranslation("read_message") .
                      "</button>";
           });

        Tag::create("{PROFILE_PAGE:USER_PM_READER}")
            ->setProcessingFunction(function () use ($user): string {
                if (RouteAgent::getCurrentRoute()->getName() != "profile-page-pm") {
                    return '';
                }

                $pmID = RouteAgent::getCurrentRoute()->getParameter("pm");
                if (!$pmID) {
                    return '';
                }

                $message = $user->MessageManager()->read($pmID);
                if ($message == false) {
                    return 'Данное сообщение не найдено.';
                }

                Tag::create("{PROFILE_PAGE:USER_SENDER_LINK}")
                    ->setProcessingFunction(function () use ($message): string {
                        return RouteAgent::buildRoute('profile-page-defined', $message['senderUID']);
                    });
                Tag::create("{PROFILE_PAGE:USER_RECEIVER_LINK}")
                   ->setProcessingFunction(function () use ($message): string {
                       return RouteAgent::buildRoute('profile-page-defined', $message['receiverUID']);
                    });
                Tag::create("{PROFILE_PAGE:USER_SENDER_NICKNAME}")
                   ->setProcessingFunction(function () use ($message): string {
                       return RouteAgent::buildRoute('profile-page-defined', UserAgent::getUserNick($message['receiverUID']));
                   });
                Tag::create("{PROFILE_PAGE:USER_RECEIVER_NICKNAME}")
                   ->setProcessingFunction(function () use ($message): string {
                       return RouteAgent::buildRoute('profile-page-defined', UserAgent::getUserNick($message['receiverUID']));
                   });
                Tag::create("{PROFILE_PAGE:USER_PM_SUBJECT}")
                   ->setProcessingFunction(function () use ($message): string {
                       return htmlentities($message['subject']);
                   });
                Tag::create("{PROFILE_PAGE:USER_PM_RECIEVETIME}")
                   ->setProcessingFunction(function () use ($message): string {
                       return Engine::DatetimeFormatToRead($message['receiveTime']);
                   });
                Tag::create("{PROFILE_PAGE:USER_PM_TEXT}")
                   ->setProcessingFunction(function () use ($message): string {
                       return htmlentities($message['text']);
                   });

                return BuildAgent::includeFromTemplateAndCompile('profile/user_pm_read.html', true);
            });

        return BuildAgent::includeFromTemplateAndCompile("profile/user_pm.html", true);
    });