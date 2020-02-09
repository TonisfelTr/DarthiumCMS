$("div.div-spoiler").on("click", function() {
    var spoiler =  $(this).find("div");
    if ($(spoiler).is(":hidden"))
        $(spoiler).show();
    else
        $(spoiler).hide();
});