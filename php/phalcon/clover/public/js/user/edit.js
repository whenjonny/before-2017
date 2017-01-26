$(function(){

    $(".comment-head .ranking-sex").click(function(){
        if($(this).attr("data-id") != $("#_uid").val()){
            return false;
        }

        Common.toggle_modal('edit_profile_modal', true);
        $("#edit_profile_modal #modal_avatar").attr("src", $("#_avatar").val());
        $("#edit_profile_modal input[name='nickname']").val($("#_nickname").val());
        var sex = $("#_sex").val() == ""? 1 : $("#_sex").val();
        $("input[name='sex'][value='"+sex+"']").click();
    });

    Common.upload("#modal_avatar_uploadify", function(data){
        $("#edit_profile_modal #modal_avatar").attr("src", data.data.url);
        $("#edit_profile_modal input[name='upload_id']").val(data.data.id);

        var options = {
            preview_id: "#modal_avatar",
            aspectRatio: 1
        };

        $("#edit_profile_modal #modal_crop").show();
        $("#edit_profile_modal #modal_crop").unbind('click').click(function(){
            $(this).unbind('click');

            var obj = {};
            obj.scale   = Common.jcrop_api.tellScaled();
            obj.bounds  = Common.jcrop_api.getBounds();
            obj.upload_id = $("#edit_profile_modal input[name='upload_id']").val();
            Common.jcrop_destroy();

            $.post("/image/crop", obj, function(data){
                data = data.data;
                $("#edit_profile_modal #modal_avatar").attr("src", data.url);
                $("#edit_profile_modal #modal_avatar").css("height", "");
                $("#edit_profile_modal #modal_avatar").css("width", "");
                $("#edit_profile_modal input[name='upload_id']").val(data.id);
            });
        });

        Common.crop(options, function(){
            Common.jcrop_api = this;
            Common.jcrop_release();
        });
    
    }, null, {url: '/image/preview'});

    $("#edit_profile_modal .close").click(function(){
        Common.toggle_modal('edit_profile_modal');
    });

    $("#edit_profile_modal #modal_before_submit").click(function(){
        var data = $("#edit_profile_modal form").serialize();

        $.post("/user/save", data, function(data){
            if (data.ret == 0) {
                alert(data.info);
            }
            else {
                alert(data.info);
            }
            location.reload();
        });
    });
});
