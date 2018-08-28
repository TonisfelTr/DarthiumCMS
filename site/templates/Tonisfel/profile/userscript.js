var needToReload = false;
setInterval(function() {
    notifyNewNotifications();
}, 5000);

$("#profile-edit-avatar-btn").on("click", function(){
    $("#profile-profile-form-form").prop("action", "/site/scripts/pmanager.php?r=" + letterId);
});

function submitForReadLetter(letterId){
    $("#profile-profile-form-form").prop("action", "/site/scripts/pmanager.php?r=" + letterId);
    $('#profile-profile-form-form').submit();
}

function insertBBCode(openTag, notNeedClose = false, texterElement = null){
    if (texterElement == null)
        var texter = document.getElementById("profile-pm-text");
    else
        var texter = document.getElementById(texterElement);
    startText = texter.value.substring(0, texter.selectionStart);
    endText = texter.value.substring(texter.selectionEnd, texter.value.length);
    tagingText = texter.value.substring(texter.selectionStart, texter.selectionEnd);
    startPos = texter.selectionStart;
    endPos = texter.selectionEnd;
    startText += '[' + openTag + ']';
    if (notNeedClose === false) endText = '[\/' + openTag + ']' + endText;
    if (notNeedClose !== false && notNeedClose !== true) endText = '[\/' + notNeedClose + ']' + endText;
    texter.value = startText + tagingText + endText;
    texter.focus();
    texter.setSelectionRange(startPos + (2 + openTag.length), endPos + (2 + openTag.length));

    //texter.value.insert
}
function notifyNewNotifications(){
    $.ajax({
        type : "POST",
        url : "/site/scripts/ajax/usernotificationsajax.php",
        success : function(data){
            var count = (data > 10) ? "10+" : data;
            if ($("#profile-btn-notifications-counter-span").html() != count) {
                if (!needToReload) needToReload = true;
                $("#profile-btn-notifications-counter-span").html(count);
                $("#profile-btn-notifications-counter-span").attr("class", "profile-btn-new-counter");
                $("#profile-notification-warning-border").show();
            }
        }
    });
}

$("#profile-pm-watch-btn").on("click", function(){
    $.ajax({
        type: 'POST',
        url: '/site/scripts/ajax/prewatchajax.php',
        data: 'text=' + $("#profile-pm-text").val(),
        success: function(data){
            $('#profile-pm-write-see').html(data);
            $("#profile-pm-write-see").show();
        }
    });
});

$("#profile-change-pass-checkbox").on("change", function(){
    if ($("#profile-change-pass-checkbox").prop("checked")){
        $("#profile-change-pass-div").find("input:password").each(function() { this.disabled = false; });
    } else {
        $("#profile-change-pass-div").find("input:password").each(function() { this.disabled = true; });
    }
});

$("#profile-edit-blacklist-nickname").on("keyup", function() {
    setInputElement($("#profile-edit-blacklist-nickname"));
    if ($(inputElement).val().length >= 2)
        showPopMenu();
    else hidePopMenu();
});
$("#profile-pm-receiver-input").on("keyup", function() {
    setInputElement($("#profile-pm-receiver-input"));
    if ($(inputElement).val().length >= 2)
        showPopMenu();
    else hidePopMenu();
});
$("#profile-friend-nickname-add-input").on("keyup", function() {
    setInputElement($("#profile-friend-nickname-add-input"));
    if ($(inputElement).val().length >= 2)
        showPopMenu();
    else hidePopMenu();
});

$("#profile-pm-write-btn").on("click", function(){
    $("#profile-profile-pm").children().hide();
    $("#profile-pm-panel-btn").show();
    $("#profile-pm-write").show();

    $("#profile-pm-panel-btn").children().removeClass("active");
    $("#profile-pm-write-btn").addClass("active");
});

$("div[data-div-title=btn-parent] > button").on("click", function (){
    $("div[data-div-title=btn-parent] > button").removeClass("active");
    $(this).addClass("active");
});