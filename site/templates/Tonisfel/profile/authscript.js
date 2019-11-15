$("#profile_auth_signup").on("click", function(){
   showPanel("signup");
   $("#profile-form-name").html("Правила");
});

$("#profile_reg_rules_reject_btn").on("click", function() {
    showPanel("auth");
    $("#profile-form-name").html("Авторизация");
});

$("#profile_auth_password_restore").on("click" ,function (){
   showPanel("remaind");
    $("#profile-form-name").html("Восстановление пароля");
});

$("#profile-auth-for-email").change(function() {
    if (this.checked) {
        $("#profile-password-remained-email").show();
        $("#profile-password-remained-nickname").hide();
    }
});

$("#profile_auth_signin").on("click", function(){
   showPanel("auth");
    $("#profile-form-name").html("Авторизация");
});

$("#profile-auth-for-nickname").change(function() {
    if (this.checked) {
        $("#profile-password-remained-nickname").show();
        $("#profile-password-remained-email").hide();
    }
});

$("#profile_reg_rules_confirm_btn").on("click", function() {
    showSubpanel("signup", 2);
    $("#profile-form-name").html("Регистрация");
    if ($("#profile-reg-error-div").html().trim() == "")
        $("#profile-reg-error-div").hide();
});

$("#profile-reg-toauth-btn").on("click", function() {
    showPanel("auth");
    $("#profile-form-name").html("Авторизация");
});

$("#profile_auth_toindex").on("click", function() {
    document.location.href = "index.php";
});

$("#profile-reg-reg-btn").on("click", function() {
    var nickname = $("#profile-reg-nickname-input").val();
    var email = $("#profile-reg-email-input").val();
    var pwd = $("#profile-reg-password-input").val();
    var repwd = $("#profile-reg-repassword-input").val();
    var captcha = $("#profile-reg-captcha-input").val();
    clearErrorRegDiv();
    $.ajax({
        type: 'POST',
        url: './site/scripts/ajax/regajax.php',
        data: 'nickname=' + nickname +
        '&email=' + email +
        '&password=' + pwd +
        '&rePassword=' + repwd +
        '&referer=' + $("#profile-reg-referer-input").val(),
        success: function(data) {
            function explode(string, delimiter) {
                var wstr = string;
                var r = [];
                var index = 0;
                if (wstr.indexOf(delimiter) == -1) {
                    r[0] = wstr;
                    return r;
                }
                while (wstr.indexOf(delimiter) != -1) {
                    r[index] = wstr.substr(0, wstr.indexOf(delimiter));
                    wstr = wstr.substr(wstr.indexOf(delimiter) + 1, wstr.length);
                    index += 1;
                }
                r[index] = wstr.substr(0, wstr.length);
                return r;
            }

            if ((data != "ok,ok,ok;ok,ok,ok;ok,ok;null;") &&
                ( data !="ok,ok,ok;ok,ok,ok;ok,ok;ok;")) {

                var nicknameErrors = explode(data.substr(0, data.indexOf(";")), ",");
                data = data.substr(data.indexOf(";") + 1, data.length);
                var emailErrors = explode(data.substr(0, data.indexOf(";")), ",");
                data = data.substr(data.indexOf(";") + 1, data.length);
                var passwordErrors = explode(data.substr(0, data.indexOf(";")), ",");
                data = data.substr(data.indexOf(";") + 1, data.length);
                var refererErrors = data.substr(0, data.indexOf(";"));
                data = data.substr(data.indexOf(";") + 1, data.length);

                showErrorRegDiv();
                if (nicknameErrors[0] == "not_set") {
                    addToErrorsList("warning-sign", "Вы не ввели никнейм.");
                }
                if (nicknameErrors[0] == "invalid_size") {
                    addToErrorsList("warning-sign", "Никнейм не может быть короче 4 символов и длиннее 16.");
                }
                if (nicknameErrors[1] == "invalid_nickname") {
                    addToErrorsList("remove", "Никнейм может содержать только цифры, символы латиницы, нижнее подчёркивание и тире.");
                }
                if (nicknameErrors[2] == "exists_nickname") {
                    addToErrorsList("remove", "Данный никнейм уже зарегистрирован в системе!");
                }
                if (emailErrors[0] == "not_set") {
                    addToErrorsList("warning-sign", "Вы не ввели адрес электронной почты.");
                }
                if (emailErrors[0] == "too_small") {
                    addToErrorsList("remove", "Вы ввели некорректный адрес электронной почты: он слишком мал.");
                }
                if (emailErrors[1] == "invalid_email") {
                    addToErrorsList("remove", "Вы ввели некорректный адрес электронной почты.");
                }
                if (emailErrors[2] == "is_exists") {
                    addToErrorsList("remove", "Данный адрес электронной почты уже зарегистрирован в системе!");
                }
                if (passwordErrors[0] == "not_set") {
                    addToErrorsList("warning-sign", "Вы не ввели пароль.");
                }
                if (passwordErrors[0] == "too_small") {
                    addToErrorsList("remove", "Введённый пароль слишком мал. Он должен быть больше 7 символов в длину.");
                }
                if (passwordErrors[1] == "not_equal") {
                    addToErrorsList("warning-sign", "Введённые пароли не совпадают.");
                }
                if (refererErrors == "not_exists") {
                    addToErrorsList("remove", "Пользователя-реферера с таким никнеймом нет в системе.");
                }
                if (refererErrors == "invalid_referer") {
                    addToErrorsList("remove", "Нельзя быть реферером самого себя.");
                }
                if (captcha == "") {
                    addToErrorsList("warning-sign", "Вы не ввели капчу.");
                }

            } else {
                hideErrorRegDiv();
                $("#profile-reg-form-form").submit();
            }
        }
    });
});

$("#profile_auth_remaind_email").on("click", function() {
    $.ajax({
        type : "POST",
        url : "/site/scripts/ajax/emailremainderajax.php",
        data: "profile-auth-for-email-input=" + $("#profile-auth-for-email-input").val(),
        success : function(data){
            var pText = $("#profile-email-remainder");
            pText.show();
            switch (data) {
                case "not exist":
                    pText.html("Данный адрес электроной почты не зарегистрирован в системе.");
                    break;
                case "not sended":
                    pText.html("Не удалось изменить пароль. Пожалуйста, свяжитесь с Aдминистрацией.");
                    break;
                default:
                    pText.html("На указанный Вами адрес электронной почты была отослана инструкция по восстановлению пароля.");
                    break;
            }

        }
    });
});

$("#profile_auth_remaind_nickname").on("click", function() {
   $.ajax({
         type: "POST",
         url: "/site/scripts/ajax/emailremainderajax.php",
         data: "profile-auth-for-nickname-input=" + $("#profile-auth-for-nickname-input").val(),
         success: function(data){
             var pText = $("#profile-email-remainder");
             pText.show();
             switch (data) {
                 case "not exist":
                     pText.html("Данный никнейм не зарегистрирован в системе.");
                     break;
                 case "not sended":
                     pText.html("Не удалось изменить пароль. Пожалуйста, свяжитесь с Aдминистрацией.");
                     break;
                 default:
                     pText.html("На адрес электронной почты " + data + " была отослана инструкция по восстановлению пароля.");
                     break;
             }
         }
   });
});