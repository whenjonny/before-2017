$(function(){
    $(".picture-hot a.new").click(function(){
        var id     = $(this).attr("data");
        var type   = $(this).attr("type");
        var self = $(this);

        var replier = $(this).parents('div.section-wrok');
        var replier_avatar = replier.find('span.ranking-sex > img').attr('src');

        // var dl = $(this).attr('isdownload');
        //alert(dl);
        // $("#view_ask_modal .upload").attr("has_dl", dl)
        var image = $(this).children('img').attr('src');
        $.get("/api/detail", {id: id, type: type}, function(data){
            var ask = data.data.data;
            var asker = data.data.user;

            $("#add_reply_modal").attr("ask_id", id);
            $("#view_ask_modal .modal-avatar").attr("src", replier_avatar);
            $("#view_ask_modal .modal-time").text(ask.create_time);
            $("#view_ask_modal .modal-image").attr("src", image);
            $("#view_ask_modal .modal-image").attr("ask_id", id);
            $("#view_ask_modal .upload").attr("has_dl", ask.has_dl||0);

            if(ask.type == 2){
                $(".modal-header a").hide();
            }
            else {
                $(".modal-header a").show();
            }
            if( self.parents('div.section-list.reply_list').length + self.parents('div.personal').length!=0 ){
                $('#view_ask_modal div.modal-header h3').text('预览');
            }
            else{
                $('#view_ask_modal div.modal-header h3').text('求P详情');
            }
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
        if($(this).attr("has_dl")=='0'){
            alert('请先下载原图才能上传作品');
           return false;
        }
        Common.toggle_modal('view_ask_modal');
        Common.toggle_modal('add_reply_modal', true);

        return false;
    });

    $('#view_ask_modal .download').click(function(){
        if($('#_uid').val()== '') {
           alert('请先登录！');
           return false;
        }
        var ask_id = $("#view_ask_modal .modal-image").attr("ask_id");
        $.get("/user/record?type=ask&target="+ask_id, function(data){
            if(data.ret == 1) {
                var url = data.data.url;
                location.href ='/user/download?type=ask&target='+ask_id+'&url='+url;
                $("#view_ask_modal .upload").attr("has_dl", 1);
            }
        });

        return false;
    });
});
