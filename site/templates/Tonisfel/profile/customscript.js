function changeTitle(newValue){
    $("#profile-form-name").animate({
        width: "1px"
    }, 1000, function() {
        $("#profile-form-name").html(newValue);
        $("#profile-form-name").animate({width: "15%"}, 1000);
    });
}
function clearErrorRegDiv(){
    $("#profile-reg-error-div").html("");
}
function showErrorRegDiv(){
    $("#profile-reg-error-div").show();
}
function hideErrorRegDiv(){
    $("#profile-reg-error-div").hide();
    $("#profile-reg-message-div").hide();
}
function addToErrorsList(icon, text) {
    var div = $("#profile-reg-error-div");
    div.append('<span class="glyphicon glyphicon-' + icon + '"></span> ' + text + '<br>');
}

function showPanel(divTitle){
    $("#profile-profile-panels > div").hide();
    $("#profile-profile-panels div[data-div-title=" + divTitle + "]").show();
    showFirstSubpanel(divTitle);
}

function showFirstPanel(){
    $("#profile-profile-panels > *").hide();
    $("#profile-profile-panels :first-child").show();
}

function showSubpanel(parentDivTitle, childNumber){
    var parentDiv = $("div[data-div-title=" + parentDivTitle + "] > div[data-div-title=body]");
    $(parentDiv).children("div").hide();
    $(parentDiv).children("div :nth-child(" +childNumber+ ")").show();
}

function showFirstSubpanel(parentDivTitle){
    var parentDiv = $("div[data-div-title=" + parentDivTitle + "] > div[data-div-title=body]");
    $(parentDiv).children("div").hide();
    $(parentDiv).children("div :first-child").show();
}