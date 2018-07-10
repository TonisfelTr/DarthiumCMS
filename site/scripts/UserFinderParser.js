var inputElement;
var menuDiv = null;
var menuList = null;
var ajaxResponse;
var ajaxResponseSize;
var LastAjaxResponse = "";

function setInputElement(JQObjectInputElement){
        inputElement = JQObjectInputElement;

}

function createPopMenu() {
    var p = $(inputElement).offset();
    menuDiv = document.createElement("div");
    menuList = document.createElement("ul");
    $(menuDiv).css({
        "background": "#fff",
        "z-index": "10",
        "position": "absolute",
        "width": "400",
        "min-height": "50",
        "padding": "5px",
        "border": "1px solid #202020",
        "box-shadow" : "3px 7px 10px #a2a2a2",
        "top" : p.top + $(inputElement).innerHeight(),
        "left" : p.left + 3
    });
    $(menuList).css("padding", "5px");
    $(menuDiv).prepend(menuList);
    $("body").append($(menuDiv));
}


function showPopMenu(){
    if ($(inputElement).val() == "") return;

    if (menuDiv == null){
        createPopMenu();
    }

    InjectLines();
    $(menuDiv).show();
}

function PopMenuClear(){
    $(menuList).html("");
}

function hidePopMenu(){
    $(menuList).html("");
    $(menuDiv).hide();
    menuDiv = null;
}

function ParseAndInject(str){
    //If Error?
    if (str == "Error!"){
        PopMenuClear();
        menuLine = document.createElement("li");
        $(menuLine).css("color", "#888");
        $(menuLine).html("<center>Ни одного пользователя не найдено!</center>");
        $(menuDiv).children("ul").append(menuLine);
        return;
    }
    var WorkStr = str;
    var SubWorkStr;
    var result = [];
    //Extract custom array.
    if (LastAjaxResponse == str && $(inputElement).val().length <= 1){
        return;
    }
    LastAjaxResponse = str;
    PopMenuClear();
    ajaxResponseSize = WorkStr.substring(WorkStr.indexOf("a")+2, WorkStr.indexOf(":", WorkStr.indexOf(":")+1));
    WorkStr = WorkStr.substring(WorkStr.indexOf("{")+1, WorkStr.lastIndexOf("}"));
    while (true) {
        if (WorkStr.length == 0) break;
        //Start the index for array;
        var i = 0;
        //Extract first subarray object from array.
        SubWorkStr = WorkStr.substring(WorkStr.indexOf("{") + 1, WorkStr.indexOf("}", WorkStr.indexOf("{") + 1));
        result[i] = [];

        result[i][0] = SubWorkStr.substring(SubWorkStr.indexOf("\"")+1, SubWorkStr.indexOf("\"", SubWorkStr.indexOf("\"")+1));
        SubWorkStr = SubWorkStr.substring(SubWorkStr.indexOf(";", SubWorkStr.indexOf(";")+1)+1, SubWorkStr.length);

        result[i][1] = SubWorkStr.substring(SubWorkStr.indexOf("\"")+1, SubWorkStr.indexOf("\"", SubWorkStr.indexOf("\"")+1));
        SubWorkStr = SubWorkStr.substring(SubWorkStr.indexOf(";", SubWorkStr.indexOf(";")+1)+1, SubWorkStr.length);

        result[i][2] = SubWorkStr.substring(SubWorkStr.indexOf("\"")+1, SubWorkStr.indexOf("\"", SubWorkStr.indexOf("\"")+1));
        SubWorkStr = SubWorkStr.substring(SubWorkStr.indexOf(";", SubWorkStr.indexOf(";")+1)+1, SubWorkStr.length);

        result[i][3] = SubWorkStr.substring(SubWorkStr.indexOf("\"")+2, SubWorkStr.indexOf("\"", SubWorkStr.indexOf("\"")+2));
        SubWorkStr = SubWorkStr.substring(SubWorkStr.indexOf(";", SubWorkStr.indexOf(";")+1)+1, SubWorkStr.length);

        if (SubWorkStr.length != 0){
            WorkStr = WorkStr.substring(WorkStr.indexOf("}")+1, WorkStr.length);
            i++;
        } else break;
    }

    ajaxResponse = result;

    for (k=0; k < ajaxResponseSize; k++){
        var avatar = document.createElement("img");
        var nickname = document.createElement("a");
        var group = document.createElement("p");

        avatar.src = result[i][0];
        nickname.innerHTML =result[i][1];
        group.innerHTML = result[i][2];

        nickname.href = "profile.php?uid=" + result[i][3];

        $(avatar).css({
            "float" : "left",
            "height": "20px",
            "width" : "20px"
        });

        $(nickname).css({
            "color" : "black",
            "top" : "0",
            "margin" : "5px 5px 5px",
            "font-size" : "17px"
        });
        $(group).css({
            "color" : "#bbb",
            "padding" : "0",
            "margin" : "0"
        });

        menuLine = document.createElement("li");
        $(menuLine).append(avatar, nickname, group);
        $(menuLine).on("click", function(){
            $(inputElement).val(nickname.innerHTML);
            hidePopMenu();
        });
        $(menuDiv).children("ul").append(menuLine);
    }

    $(menuDiv).find("li").css({
        "border-top" : "1px solid #bbb",
        "border-bottom" : "1px solid #bbb",
        "padding" : "10px"
    });
    $(menuDiv).find("li").hover(function() {
        $(this).backgroundColor = "#aaaaaa";
    });
}

function InjectLines(){
    $.ajax({
        type: "POST",
        url: "/site/scripts/ajax/usersearchajax.php",
        data: "nickname=" + $(inputElement).val(),
        success: function(data) {
             ParseAndInject(data);
       }
    });
}

