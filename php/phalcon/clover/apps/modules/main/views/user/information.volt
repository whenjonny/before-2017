<style>
.login-control .controls #uploadify {
    position: relative;
    left: 0;
    top: 0;
}
div.avatar-size img {
    max-width: 200px;
}
div.portrait-size img {
    width: 120px;
    height: 120px;
    overflow: hidden;
    border-radius: 50%;
}
</style>
<div class="login-container">
	<div class="hero-unit">
		<div class="login-header">
			<h1>完善个人资料</h1>
		</div>
		<form action="information" enctype="multipart/form-data" method="POST" id="form_info">
			
			<div class="login-control">
				<label class="control-label" for="avatar">头像</label>
				<div class="controls avatar-size portrait-size">
					<img src="/img/avatar.jpg" alt="" id="avatar">
					<input id="upload_id" name="upload_id" type="hidden">
				</div>
				<div class="controls">
                    <input id="uploadify" type="file" class="select-head">

                    <button id="crop" type="button">确认截图</button>
				</div>
			</div>
			<div class="login-control">
				<label class="control-label" for="nickname">昵称</label>
				<div class="controls">
					<input type="text" name="nickname" placeholder="昵称">
				</div>
			</div>
			<div class="login-control">
                <label class="control-label col-md-2 login-control-padding">性别</label>
                <div class="col-md-9 controls">
                    <div class="radio-list ">
                   		 <label class="inline margin-sex">
                            <input type="radio" name="sex" value="1" checked="" class="select-sex"> 
                            男
                        </label>
                        <label class="inline margin-sex">
                            <input type="radio" name="sex" value="0" class="select-sex">
                            女
                        </label>
                        
                    </div>
                </div>
            </div>
			<div class="text-align" >
				<button type="button"  class="btn btn-info btn-large comfim-information" id="before_submit">确认完善资料</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
$(function(){
    $('#crop').hide();
    Common.upload("#uploadify", function(data){
        $("#avatar").parent('div').removeClass('portrait-size');
        $('#crop').click();
        $("#avatar").attr("src", data.data.url);
        $("#upload_id").val(data.data.id);
        var options = {
            preview_id: "#avatar",
            aspectRatio: 1
        };

        $("#crop").show();
        $("#crop").unbind('click').click(function(){
            $(this).unbind('click');

            var obj = {};
            obj.scale   = Common.jcrop_api.tellScaled();
            obj.bounds  = Common.jcrop_api.getBounds();
            obj.upload_id = $("#upload_id").val();
            Common.jcrop_destroy();

            $.post("/image/crop", obj, function(data){
                data = data.data;
                $("#avatar").attr("src", data.url);
                $("#avatar").css("height", "");
                $("#avatar").css("width", "");
                $("#upload_id").val(data.id);
                $("#avatar").parent('div').addClass('portrait-size');
            });
        });

        Common.crop(options, function(){
            Common.jcrop_api = this;
            Common.jcrop_release();
        });
        
    }, null, {url: '/image/preview'});

    $('#before_submit').click( function(){
        if($.trim($("input[name='nickname']")[0].value) === '') {
            alert('请输入昵称！');
            return false;
        } else {
            $('#form_info').submit();
        }
    });
});
</script>
