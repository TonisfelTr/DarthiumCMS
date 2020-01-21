$("img.img-for-frame").click(function (){
    var src = $(this).attr("src");
   $("div.imager-background").show();
   $("#image").attr("src", src);
   $("#image_save").attr("href", src);
   $("#image_see_full").attr("href", src);
});

$("#imager_close").click(function (){
    $("div.imager-background").hide();
});

