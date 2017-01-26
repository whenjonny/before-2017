var options;
var submited = false;
$(function(){
    options = {
        preview_id: "#ask_modal_preview",
        aspectRatio: 3/4
    };
    
    function active_step_two(){
        Common.jcrop_destroy();
        //$("#ask_modal_preview").removeAttr("upload_id");
        $("#ask_modal_preview").removeAttr("width");
        $("#ask_modal_preview").removeAttr("height");
        $("#ask_modal_preview").removeAttr("alt");
        $("#ask_modal_preview").removeAttr("style");
        $("#ask_modal_preview").css("width", "");
        $("#ask_modal_preview").css("height", "");

        
        $("#ask_modal_labelboard .label-re").remove();

        $(".upload-step").removeClass("upload-active");
        $("#step-two").addClass("upload-active");

        $("#ask_modal_crop").unbind('click').click(function(){
            $(this).unbind('click');

            var obj = {};
            obj.scale   = Common.jcrop_api.tellScaled();
            obj.bounds  = Common.jcrop_api.getBounds();
            obj.upload_id = $("#ask_modal_preview").attr("upload_id");

            $.post("/image/crop", obj, function(data){
                data = data.data;
                $("#ask_modal_label").attr("src", data.url);
                $("#ask_modal_label").attr("data", data.id);

                Common.resize("#ask_modal_container", 300, data.ratio);
                active_step_three();
            });
        });

        Common.crop(options, function(){
            Common.jcrop_api = this;
            Common.jcrop_release();
        });

        $('#ask_modal_crop').css('background','#26A9F8');
        $('#ask_modal_label').css('display','none');
        $('.crop').show();
    }

    function active_step_three(){
        $(".upload-step").removeClass("upload-active");
        $("#step-three").addClass("upload-active");

        $('#ask_modal_crop').css('background','#797979').text("裁剪完成");
        $('#ask_modal_label').css('display','inline-block');
        $('.crop').hide();

        Common.jcrop_destroy();
        $("#ask_modal_preview").hide();
    }

    function active_step_four(){
        $(".upload-step").removeClass("upload-active");
        $("#step-four").addClass("upload-active");
    }

    Common.upload("#ask_uploadify", function(data){
        Common.preview('ask_modal_preview', data);

        Common.resize("#ask_modal_container", 300, data.data.ratio);
        active_step_two();
    });

    Common.label('ask_modal_label', {
        div: '<div class="label-re">' +
                '<div class="triangle"></div>' +
                '<div class="breathe"></div>' +
                '<div class="label-result"></div>' +
                '<input type="text" class="label-font" placeholder="填写你要的效果">' +
            '</div>',
        offset_div: 'ask_modal_labelboard'
    }); 

    $("#ask_modal_label").click(function(){
        active_step_four();
    });

    $("#add_ask_modal .crop").click(function(){
        if(Common.jcrop_api == undefined) {
            return false;
        }
        if(!$("#step-two").hasClass("upload-active")){
             return false;
        }
        var ratio = eval($(this).attr("data"));
        if(ratio == "0"){
            options.aspectRatio = 1;
            Common.jcrop_release();
        }
        else {
            options.aspectRatio = ratio;
            Common.crop(options);
        }

        $(this).siblings().removeClass('color-class');
        $(this).addClass('color-class');
    });

    $("#add_ask_modal .close").click(function(){
        Common.toggle_modal('add_ask_modal', true);
    });

    $("#add_ask_modal #ask_modal_save").click(function(){
        if( submited ){
            return;
        }
        submited = true;

        var obj = {};
        obj.upload_id = $("#ask_modal_preview").attr("upload_id");
        obj.labels = [];

        var label_img_div = $("#ask_modal_label");
        var width  = parseFloat(label_img_div.css("width"));
        var height = parseFloat(label_img_div.css("height"));

        var offset = label_img_div.offset();
        var labels = $("#ask_modal_labelboard .label-re");

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

        if(obj.labels.length == 0){
            alert("请至少添加一个标签");
            return false;
        }

        $.post("/ask/save", obj, function(data){

            if (data.ret == 0) {
                alert(data.info);
                submited = false;
            }
            else {
                alert(data.info);
            }
            location.reload();
        });
    });
});
