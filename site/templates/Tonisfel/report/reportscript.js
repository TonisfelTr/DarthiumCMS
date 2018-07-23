function insertBBCode(openTag, notNeedClose = false, texterElement = null){
    if (texterElement == null)
        var texter = document.getElementById("report-add-message");
    else
        var texter = texterElement;
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

$("#report-input-div").hide();
var e = document.getElementById("report-theme-selector");
$("#report-theme-selector").on("change", function() {
    var selectedVal = e.options[e.selectedIndex].value;
    if (selectedVal === "other") {
        $("#report-select-theme-status").val("0");
        $("#report-selector-div").hide();
        $("#report-input-div").show();
    }
});
$("#report-return-selector-btn").on("click", function() {
    $("#report-select-theme-status").val("1");
    $("#report-selector-div").show();
    $("#report-input-div").hide();
    e.selectedIndex = 0;
});

$("#report-btn-b").click(function() {
    insertBBCode("b");
});
$("#report-btn-u").click(function() {
    insertBBCode("u");
});
$("#report-btn-i").click(function() {
    insertBBCode("i");
});
$("#report-btn-s").click(function() {
    insertBBCode("s");
});
$("#report-btn-align-center").click(function() {
    insertBBCode("align=center", "align");
});
$("#report-btn-align-left").click(function() {
    insertBBCode("align=left", "align");
});
$("#report-btn-align-right").click(function() {
    insertBBCode("align=right", "align");
});
$("#report-btn-youtube").click(function() {
    insertBBCode("youtube=", true);
});
$("#report-btn-img").click(function() {
    insertBBCode("img=", true);
});
$("#report-btn-hr").click(function() {
    insertBBCode("hr", true);
});
$("#report-btn-upload").click(function() {
    $("#uploader-main").show();
});