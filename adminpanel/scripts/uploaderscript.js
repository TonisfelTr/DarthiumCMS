var checkedFilesCount = 0;
var texterID = "";

$("#uploader-close-btn").click(function() {
    $("#uploader-form").hide();
});

$("#uploader-table-select-all").change(function(){
    if ($("#uploader-table-select-all").is(":checked")){
        $("input[type=checkbox]").prop("checked", true);
        checkedFilesCount = $("input[type=checkbox]:checked").length -1;
    } else {
        $("input[type=checkbox]:checked").prop("checked", false);
        checkedFilesCount = 0;
    }
});

$("input[type=checkbox]").change(function(){
    checkedFilesCount = $("input[type=checkbox]:checked").length;
    if ($("#uploader-table-select-all").is(":checked"))
        checkedFilesCount--;

    if ($("#uploader-counter-div").is(":hidden") && checkedFilesCount != 0){
        $("#uploader-counter-div").show();
        $("#uploader-file-manipulator-div > button").prop("disabled", false);
    } else {
        if (checkedFilesCount <= 0 ) {
            $("#uploader-counter-div").hide();
            $("#uploader-file-manipulator-div > button").prop("disabled", true);
        }
    }

    $("#uploader-selected-counter").html(checkedFilesCount);

    var checkedFilesID = "";
    $("input[type=checkbox]:checked").each(function(){
       if ($(this).data("file-id") != undefined) {
            checkedFilesID += $(this).data("file-id") + ",";
       }
    });
    checkedFilesID = checkedFilesID.substring(0, checkedFilesID.length-1);
    $("#uploader-file-delete-ids").val(checkedFilesID);
});
