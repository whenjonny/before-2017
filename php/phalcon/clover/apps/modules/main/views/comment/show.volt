<div class="container">
    <?php $__dealUserInfo = $this->view->ownerUserInfo; ?>
    {% include "user/simpleInfoBar.volt" %}
    <div class="content-comment">
        <div class="section-list">
            <?php $__replyInfo = $model; ?>
            <?php
				//$rpic  = watermark2($__replyInfo['image_url']);
				//$rpic .= '||'.watermark2(url_cut_tail($__replyInfo['image_url']));
				$rpic = watermark2(url_cut_tail($__replyInfo['image_url']));
                $rpic = urlencode($rpic);
				$rtext = $__replyInfo['desc'];
                $__replyInfo['image_url'] = watermark2(url_cut_tail($__replyInfo['image_url']));
			?>
            {% include "reply/singleReply.volt" %}
       </div>
   </div>
</div>

<style>
.label {
	position: absolute;
	color: black;
}
</style>
<div class="modal hide fade in viewPhotoModal" id="viewPhotoModal" aria-hidden="false">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3>上传图片</h3>
	</div>
	<div class="modal-body" id="viewPhotoModalBody">
		<div class="upload-photo">
			<div class="isplay-photo upload-photo-style" >
				<div id="labelboard" class="labelboard-photo">
					<img src=""  id="preview" class="display-photo" >
                    <img src=""  id="label" class="label-photo-img">
<!--
					<div class="label-re">
						<div class="triangle"></div>
						<div class="breathe"></div>
						<div class="label-result"></div>
						<input type="text" class="label-font" placeholder="填写你要的效果">
                    </div>
-->
					<div class="display-photo" id="fileQueue"></div>
				</div>
			</div>
		</div>
		<div class="upload-right">
			<div class="upload-active">
				<div class="font-ones">第一步:上传图片</div>
				<div class="upload-button">
					<input type="file" id="uploadify" value="上传图片" >
				</div>
			</div>
			<div class="">
				<div class="upload-button">
					<button class="btn btn-inverse upload-button-color" id="add_reply_btn" onclick="add_reply({{ask.id}})">完成</button>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	$('.download').click(function(){
		if($('#_uid').val()== '') {
           alert('请先登录！');
           return false;
        }
        var ask_id = $(this).attr('data');

        $.get("/user/record?type=ask&target="+ask_id, function(data){
            if(data.ret == 1) {
                var url = data.data.url;
                location.href ='/user/download?type=ask&target='+ask_id+'&url='+url;
                $("#view_ask_modal .upload").attr("has_dl", 1);
            }
        });
        //location.href ='/user/download?type=ask&target='+ask_id;
        $("#upload_reply_btn").attr('hasdl','1');
        return false;
    });

	$('.download_reply').click(function(){
		if($('#_uid').val()== '') {
           alert('请先登录！');
           return false;
        }
        var reply_id = $(this).attr('data');
        $.get("/user/record?type=reply&target="+reply_id, function(data){
            if(data.ret == 1) {
                var url = data.data.url;
                location.href ='/user/download?type=reply&target='+reply_id+'&url='+url;
                $("#view_ask_modal .upload").attr("has_dl", 1);
            }
        });
        //location.href ='/user/download?type=reply&target='+ask_id;
        return false;
    });

    $("#upload_reply_btn").click(function(){
    	if($('#_uid').val()== '') {
           alert('请先登录！');
           return false;
        }
        if($(this).attr('hasdl') == '0'){
        	alert('请先下载原图才能上传作品');
        	return false;
        }
        var ask_id = $(this).attr('data');
        $("#add_reply_modal").attr("ask_id", ask_id);

        Common.toggle_modal('add_reply_modal');
        return false;
    });
   $('.w_input').click(function(){

   })
</script>
