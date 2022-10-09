<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/engine/classes/engine/Engine.php";
\Engine\Engine::LoadEngine();


$session = \Users\UserAgent::SessionContinue();
if ($session === TRUE)
    $user = new \Users\Models\User($_SESSION["uid"]);
else
    $user = false;


if ($user !== false && $user->UserGroup()->getPermission("sc_design_edit")){
    if (isset($_POST["savefsb"])){
        if (!empty($_POST["link"])) {
            if (!\Decorator\Controllers\BannerAgent::IsBannerExists("name", "firstbanner")) {
                $result = \Decorator\Controllers\BannerAgent::AddSmallBanner("firstbanner", $_POST["link"]);
                if ($result !== false)
                    Echo $result;
                else
                    echo "failed";
                exit;
            } else {
                $result = \Decorator\Controllers\BannerAgent::EditSmallBanner("first", $_POST["link"]);
                if ($result !== false)
                    Echo "okey";
                else
                    echo "failed";
                exit;
            }
        } else {
            echo "nls";
            exit;
        }
    }

    if (isset($_POST["removefsb"])){
        if (!empty($_POST["id"])){
            $result = \Decorator\Controllers\BannerAgent::RemoveBanner($_POST["id"]);
            if ($result === true)
                echo "okey";
            else
                echo "failed";
            exit;
        } else
            echo "nis";
        exit;
    }

    if (isset($_POST["savessb"])){
        if (!empty($_POST["link"])) {
            if (!\Decorator\Controllers\BannerAgent::IsBannerExists("name", "secondbanner")) {
                $result = \Decorator\Controllers\BannerAgent::AddSmallBanner("secondbanner", $_POST["link"]);
                if ($result !== false)
                    Echo $result;
                else
                    echo "failed";
                exit;
            } else {
                $result = \Decorator\Controllers\BannerAgent::EditSmallBanner("second", $_POST["link"]);
                if ($result !== false)
                    Echo "okey";
                else
                    echo "failed";
                exit;
            }
        } else {
            echo "nls";
            exit;
        }
    }

    if (isset($_POST["removessb"])){
        if (!empty($_POST["id"])){
            $result = \Decorator\Controllers\BannerAgent::RemoveBanner($_POST["id"]);
            if ($result === true)
                echo "okey";
            else
                echo "failed";
            exit;
        } else
            echo "nis";
        exit;
    }

    if (isset($_POST["addbbaner"])){
        if (empty($_POST["banner-name"])){
            echo "nns";
            exit;
        }
        if (empty($_POST["banner-content"])){
            echo "cns";
            exit;
        }
        $result = \Decorator\Controllers\BannerAgent::AddBigBanner($_POST["banner-name"], $_POST["banner-content"], $_POST["banner-visibility"]);
        if ($result === false)
            echo "failed";
        else
            echo $result;
        exit;
    }

    if (isset($_POST["editbbaner"])){
        if (empty($_POST["banner-name"])){
            echo "nns";
            exit;
        }
        if (empty($_POST["banner-content"])){
            echo "cns";
            exit;
        }
        $result = \Decorator\Controllers\BannerAgent::EditBanner($_POST["banner-id"], "name", $_POST["banner-name"]);
        $result = \Decorator\Controllers\BannerAgent::EditBanner($_POST["banner-id"], "content", $_POST["banner-content"]);
        $result = \Decorator\Controllers\BannerAgent::EditBanner($_POST["banner-id"], "isVisible", $_POST["banner-visibility"]);
        if ($result === false)
            echo "failed";
        else
            echo "okey";
        exit;
    }
    if (isset($_POST["getbbanner"])){
        if (empty($_POST["banner-id"])){
            echo "failed";
            exit;
        }
        $result = \Decorator\Controllers\BannerAgent::GetBannerHTML($_POST["banner-id"]);
        if ($result === false){
            echo "failed";
            exit;
        } else {
            echo $result;
            exit;
        }
        exit;
    }

    if (isset($_POST["removebbanner"])){
        if (empty($_POST["banner-id"])){
            echo "bne";
            exit;
        }
        $result = \Decorator\Controllers\BannerAgent::RemoveBanner($_POST["banner-id"]);
        if ($result) {
            echo "okey";
            exit;
        }
        else {
            echo "failed";
            exit;
        }
        exit;
    }

}

header("Location: ../../../adminpanel.php?p=staticc&res=1");
exit;