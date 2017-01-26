<!-- <script type="text/javascript" src="/uploadify/jquery.min.js"></script> -->
<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
<script type="text/javascript" src="/tapmodo/js/jquery.Jcrop.js"></script>
<link rel="stylesheet" href="/tapmodo/css/jquery.Jcrop.css" type="text/css" />
<script type="text/javascript" src="/jqdrag/jquery-drag.js"></script>
<script type="text/javascript">
var jcrop_api;
var options;
$(function(){
    var width = Common.getSelectWidth();
    options = {
        preview_id: "#preview",
        aspectRatio: 3/4,
    };

    Common.upload("#uploadify", function(data){
        jcrop_api.destroy();
        $("#preview").attr("src", data.data.url);
        $("#preview").attr("upload_id", data.data.id);
        $("#preview").attr("style", "");

        Common.crop(options, function(){
            jcrop_api = this;
        });
    });

    Common.preview('preview'); 
    Common.label('label'); 

    Common.crop(options, function(){
        jcrop_api = this;
    });
    
    $("#crop1").click(function(){
        options.aspectRatio = 3/4;
        Common.crop(options);
    });

    $("#crop2").click(function(){
        options.aspectRatio = 4/3;
        Common.crop(options);
    });

    $("#crop3").click(function(){
        options.aspectRatio = 1;
        Common.crop(options);
    });

    $("#crop4").click(function(){
        jcrop_api.release();
    });

    $("#crop_image").click(function(){
        var obj = {};
        obj.scale   = jcrop_api.tellScaled();
        obj.bounds  = jcrop_api.getBounds();
        obj.upload_id = $("#preview").attr("upload_id");

        $.post("/image/crop", obj, function(data){
            console.log(data); 
        });
    });

    $("#label_image").click(function(){
        var obj = {};
        obj.upload_id = $("#preview").attr("upload_id");
        obj.labels = [];

        var labels = $("#label div");
        for(var i = 0; i < labels.length; i++){
            obj.labels.push({
                x: parseFloat($(labels[i]).css("left")),
                y: parseFloat($(labels[i]).css("top")),
                width: parseFloat($("#label").css("width")),
                height: parseFloat($("#label").css("height")),
                text: $(labels[i]).find("input").val()
            });
        }
        console.log(obj);
    });
});
</script>
<div style="width: 400px; text-align: center">
    <img src="/images/20150224/20150224-20182154ec6c0dbf13d.jpg" id="preview" style="display: none"/>
    <div style="display: none" id="fileQueue"></div>
</div>

<input type="file" id="uploadify" />
<button class="btn btn-inverse" id="crop_image">截图</button>
<br>
<button id="crop1">3:4</button>
<button id="crop2">4:3</button>
<button id="crop3">1:1</button>
<button id="crop4">原图</button>

<style>
.label {
    position: absolute;
    color: black;
}
</style>

<div id="label" style="width: 300px; height: 300px; background: url('/images/20150224/20150224-20182154ec6c0dbf13d.jpg')">
</div>
<button class="btn btn-inverse" id="label_image">标签</button>
