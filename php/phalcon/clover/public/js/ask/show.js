$(function(){
    var width = Common.getSelectWidth();

    Common.upload("#uploadify", function(data){
            data = data.data;
            $("#label").attr("src", data.url);
            $("#label").attr("data", data.id);

            $('#select-value').css('display','none');

            $('#label').css('display','inline-block');

    }, function(){}, {'url' : '/image/upload'});

    var offset = $("#labelboard").parent().parent().offset();
    offset.top = offset.top  - 20;
    offset.left= offset.left - 30;
    Common.label('labelboard', {
        div: '<div class="label-re">' +
                '<div class="triangle"></div>' +
                '<div class="breathe"></div>' +
                '<div class="label-result"></div>' +
                '<input type="text" class="label-font" placeholder="填写你要的效果">' +
            '</div>',
        offset: offset
    }); 

    $('.upload-border').click(function(){
        $(this).siblings().removeClass('color-class');
        $(this).addClass('color-class');
    });

    $('#viewPhotoModal .close').click(function() {
        $('.modal-backdrop').hide();
        $('#viewPhotoModal').fadeOut('slow');        
    });

});

function add_reply(ask_id)
{
    var $label = $("#label");
    var upload_id  = $label.attr('data');

    if (ask_id) {
        $.ajax({
            url : '/reply/save?ask=' + ask_id,
            type: 'post',
            dataType: 'json',
            data: {
                id : upload_id,
                labels: ''
            },
            success : function(data) {
                alert(data.info);

                if (data.ret) {
                    location.reload();
                }
            }
        });
    } else {
        alert('请上传图片');
    }
}


function label() {
    var obj = {};
    obj.upload_id = $("#preview").attr("upload_id");
    obj.labels = [];
    var offset = $("#labelboard").offset();

    var labels = $("#labelboard .label-re");
    for(var i = 0; i < labels.length; i++){
        var label_offset = $(labels[i]).offset();
        obj.labels.push({
            x: label_offset.left - offset.left,
            y: label_offset.top - offset.top,
            width: parseFloat($("#label").css("width")),
            height: parseFloat($("#label").css("height")),
            text: $(labels[i]).find("input").val()
        });
    }
}


