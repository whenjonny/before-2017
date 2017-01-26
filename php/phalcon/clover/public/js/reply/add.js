var submited = false;
$(function(){
    
    function step_two(){
        $("#reply_modal_labelboard .label-re").remove();

        $(".upload-step").removeClass("upload-active");
        $("#reply-step-two").addClass("upload-active");
    }

    Common.upload("#reply_uploadify", function(data){
        //返回值为0，上传失败
        if( !data.ret ){
            alert(data.info);
            return false;
        }

        $("#reply_modal_label").attr("src", data.data.url);
        $("#reply_modal_label").show();

        $("#add_reply_modal").attr("upload_id", data.data.id);

        Common.resize("#reply_modal_container", 300, data.data.ratio);
        step_two();
    }, null, {url: '/image/upload'});

    Common.label('reply_modal_label', {
        div: '<div class="label-re">' +
                '<div class="triangle"></div>' +
                '<div class="breathe"></div>' +
                '<div class="label-result"></div>' +
                '<input type="text" class="label-font" placeholder="填写你要的效果">' +
            '</div>',
        offset_div: 'reply_modal_labelboard'
    }); 

    $("#add_reply_modal .close").click(function(){
        Common.toggle_modal('add_reply_modal');
    });

    $("#add_reply_modal #reply_modal_save").click(function(){
        if( submited ){
            return;
        }
        submited = true;

        var obj = {};
        obj.upload_id = $("#add_reply_modal").attr("upload_id");
        obj.ask_id    = $("#add_reply_modal").attr("ask_id");
        obj.download_type = $("#add_reply_modal").attr("dtype");
        obj.download_target_id = $("#add_reply_modal").attr("dtid");
        obj.labels = [];

        var label_img_div = $("#reply_modal_label");
        var width  = parseFloat(label_img_div.css("width"));
        var height = parseFloat(label_img_div.css("height"));

        var offset = label_img_div.offset();
        var labels = $("#reply_modal_labelboard .label-re");

        for(var i = 0; i < labels.length; i++){
            var label_offset = $(labels[i]).offset();
            var left = label_offset.left - offset.left;
            var top  = label_offset.top - offset.top;

            var x = left/width;
            var y = top/height;
            var content = $(labels[i]).find("input").val();

            obj.labels.push({
                x: x,
                y: y,
                content: content,
                direction: 0,
                vid: new Date().getTime()
            });
        }

        /*
        if(obj.labels.length == 0){
            alert("请至少添加一个标签");
            return false;
        }
        */

        $.post("/reply/save", obj, function(data){
            if (data.ret == 0) {
                alert(data.info);
                submited = false;
            }
            else {
                alert(data.info);
            }
            location.href = "/user/my_works";
        });
    });
});
