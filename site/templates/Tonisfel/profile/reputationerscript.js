function hideIt(){
    $("#reputation-frame").hide();
}

$("#reputation-change-btn").on("click", function(){
    var o = false;
    var t = false;
    var c = false;
    $("#reputation-errors").show();
    $("#reputation-errors").html("");
    if ($("#reputation-comment").val() == ""){
        $("#reputation-errors").html("<span class=\"glyphicon glyphicon-warning-sign\"></span> Вы не написали комментарий.<br>");
    } else o = true;
    if(document.getElementById("reputation-type").options[document.getElementById("reputation-type").selectedIndex].value == "n"){
        $("#reputation-errors").html($("#reputation-errors").html() + "<span class=\"glyphicon glyphicon-warning-sign\"></span> Вы не выбрали тип оценки.<br>");
    } else t = true;
    if($("#reputation-captcha-input").val() == ""){
        $("#reputation-errors").html($("#reputation-errors").html() + "<span class=\"glyphicon glyphicon-warning-sign\"></span> Вы не ввели капчу.<br>");
    } else c = true;
    if (o == true && t == true && c == true){
        $("#reputation-changer-form").submit();
    }
});

$("#reputation-change-form-btn").on("click", function(){
    $('#reputation-edit-form').show();
});