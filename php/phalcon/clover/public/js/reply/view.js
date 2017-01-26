$(function(){
    $(".picture-hot a").click(function(){
        var id   = $(this).attr("data");
        var type = $(this).attr("type");

        $.get("/api/detail", {id: id, type: type}, function(data){
            var ask = data.data.ask;
            var asker = data.data.asker;

            $("#view_ask_modal .modal-avatar").attr("src", asker.avatar);
            $("#view_ask_modal .modal-time").text(ask.ask_created);
            $("#view_ask_modal .modal-image").attr("src", ask.image_url);

            Common.toggle_modal('view_ask_modal');
        });
        return false;
    });

    $("#view_ask_modal .close").click(function(){
        Common.toggle_modal('view_ask_modal');
        return false;
    });

    $("#view_ask_modal").next().click(function(){
        Common.toggle_modal('view_ask_modal');
        return false;
    });

    $("#view_ask_modal .upload").click(function(){

    });
});
