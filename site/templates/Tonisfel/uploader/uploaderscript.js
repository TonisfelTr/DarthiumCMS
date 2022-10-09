$("#uploader-close-btn").click(function() {
    $("#uploader-engine").hide();
});

$('#uploader-select-all').change(function() {
    var checkboxes = $(this).closest('table').find(':checkbox');
    if($(this).is(':checked')) {
        checkboxes.prop('checked', true);
        $(".uploader-body").height(620);
    } else {
        checkboxes.prop('checked', false);
        $(".uploader-body").height(555);
    }
});

$("input:checkbox").change(function(){
    var counter = 0;
    if ($("#uploader-select-all").is(":checked")) counter = counter-1;
    $("input:checkbox:checked").each(function(){
        counter = counter+1;
    });
    if (counter > 0){
        $("#uploader-delete-file").prop("disabled", false);
        $("#uploader-selected-count-div").show();
        $("#uploader-selected-counter-span").html(counter);
    }
    else {
        $("#uploader-delete-file").prop("disabled", true);
        $("#uploader-selected-count-div").hide();
    }
});

$("#uploader-delete-file").on("click", function() {
    var formActionLink = "site/scripts/uploader.php?fdelete=";
    var comma = "";
    $("input:checkbox:checked").each(function () {
        if ($( this ).attr("data-fid-selected") != undefined){
            if (formActionLink.charAt(formActionLink.length-1) != "=") comma = ",";
            formActionLink = formActionLink + comma + $( this ).attr("data-fid-selected");
        }
    });
    $( "#uploader-delete-file" ).attr("formaction", formActionLink);
    $( "#uploader-delete-file").attr("type", "submit");
    $( "#uploader-delete-file").click();
});