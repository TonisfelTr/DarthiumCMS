<?php

include_once "engine/main.php";

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Tonisfel Tavern CMS - Установка</title>
    <link rel="stylesheet" href="install/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="../libs/js/jquery-3.1.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <link rel="icon" href="install/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 install-steps-block">
            <div class="container">
                <p>Прогресс установки:</p>
                <hr>
                <ul class="radio-btn">
                    <li class="active">Приветствие</li>
                    <li class="unmaded">Лицензионное соглашение</li>
                    <li class="unmaded">Проверка установленных плагинов</li>
                    <li class="unmaded">Проверка прав</li>
                    <li class="unmaded">База данных</li>
                    <li class="unmaded">Данные сайта</li>
                    <li class="unmaded">Установка</li>
                    <li class="unmaded">Созданине пользователя администратора</li>
                    <li class="unmaded">Завершение</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-9 col-md-6 col-sm-6 col-xs-12 install-body">
            <div class="container">
                <div id="1-page" class="active">
                    <h1>Tonisfel Tavern CMS</h1>
                    <hr>
                    <p>Приветствуем Вас в панели установки Tonisfel Tavern CMS на Ваш сайт. Для установки Вам потребуется заполнить несколько полей. Установщик проверит, установленны ли необходимые плагины,
                    проверит доступ к папкам, которые будут использоваться в процессе эксплуатации системы. От Вас же потребуется самая малость: заполнить необходимые поля, установить права на папки и установить
                    нужные плагины.</p>
                    <div class="button-footer">
                        <button class="next-step" type="button" data-next-slider="2"></button>
                    </div>
                </div>
                <div id="2-page">

                </div>
                <div id="3-page">

                </div>
                <div id="4-page">

                </div>
                <div id="5-page">

                </div>
                <div id="6-page">

                </div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript">
    $("li").on("click", function(){
        if ($(this).hasClass("maded")){
            var parent = $(this).parent("ul");
            var thisNumber = $(parent).children().indexOf($(this))+1;
            $("div.install-body > div.container > div.active").removeClass("active");
            $(this).addClass("active");
            $("div.install-body > div.container > div#"+thisNumber).addClass("active");
        }
        else if (!$(this).hasClass("active")) { alert("Вы не дошли до данной ступени установки!");}
    });

    $("button.next-step").on("click", function(){
       if ($("ul.radio-btn").children("li.maded"))
    });
</script>
</html>
