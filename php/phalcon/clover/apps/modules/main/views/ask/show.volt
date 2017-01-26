<div class="detail">
	<div class="dl-content">
		<div class="section-list reply_list">
		{% for reply in replies %}
			<?php $__replyInfo = $reply; ?>
			<?php
				$rpic  = watermark2($ask_arr['image_url']);
				$rpic .= '||'.watermark2(url_cut_tail($__replyInfo['image_url']));
                $rpic = urlencode($rpic);
				$rtext = $ask->desc;
                $__replyInfo['image_url'] = watermark2(url_cut_tail($__replyInfo['image_url']));
			?>
			{% include "reply/singleReply.volt" %}
		{% endfor %}
		</div>

		<!--  原图展现区 -->
		<div class="arrow"></div>
		<div class="section-wrok detail-sr" data-id="<?php echo $ask->id; ?>">
			<div class="mc-head padding-hot">
				<a href="/user/profile/{{ask.asker.uid}}">
				<span class="ranking-sex">
					<img src="{{ask.asker.avatar}}" alt="">
					<span class="sex bc-{% if ask.asker.sex == 1 %}blue{% else %}pink{% endif %} icon-{% if ask.asker.sex == 1 %}boy{% else %}girl{% endif %}"></span>
				</span>
				<span class="mc-name">{{ask.asker.nickname}}</span>
				</a>
				<span class="mc-time">{{ask.create_time|time_in_ago}}</span>
				<!-- <span class="img-load download_reply"><button class="download-picture" data="<?php echo $ask->id; ?>">下载原图</button></span> -->
				<span class="img-load"><button class="download-picture download" data="<?php echo $ask->id; ?>">下载原图</button></span>


			</div>
            <div class="picture-hot">
                <?php echo get_image_labels($ask_arr); ?>
			</div>
			<div class="detail-small">
	<!-- 			<div class="share-friend hover-weixin-code">
						<i class="detail-bubble icon-bubble"></i>
						<span class="bubble-amount"><?php echo $ask->weixin_share_count; ?></span>
						<div class="weixin-code">
								<img src="/img/code.jpg" alt="">
						</div>
				</div>
				<div class="share-friend click-color">
						<i class="detail-praise icon-praise"></i>
						<span class="praise-amount"><?php echo $ask->up_count; ?></span>
				</div>
				<div class="share-friend">
					<i class="speech-bubble icon-speech-bubble"></i>
					<span class="bubble-amount"><?php echo $ask->comment_count; ?></span>
				</div>
				<div class="share-friend share-friend-border">
					<span class="icon-ellipsis"></span>
				</div> -->
				<div class="master">
					<!-- <span class="master-share"></span> -->
					<?php
						$pic = watermark2($ask_arr['image_url']);
						foreach ($replies as $reply) {
							$pic .= '||'.watermark2(url_cut_tail($reply['image_url']));
						}
						$pic = urlencode($pic);
					?>
					<div class="master-wb-share">
					     <div class="sharePs">
					        <div class="share-ask" data="<?php echo $ask->id; ?>"><wb:share-button appkey="1251119895" addition="number" type="button" default_text="<?php if($ask->desc)echo $ask->desc;else echo '@求PS大神app ';?>" pic="<?php echo $pic;?>" picture_search="false"></wb:share-button></div>
					     </div>
					</div>
				</div>
			</div>
<!-- 			<div class="work-value">
				上传作品入口
				<button class="dl-upload dl-blue" data="<?php echo $ask->id; ?>" id="upload_reply_btn" hasdl="<?php echo $has_dl; ?>">上传作品</button>
			</div> -->
		</div>
		{{page}}<br />
	</div>
</div>

<style>
.label {
	position: absolute;
	color: black;
}
.comment-list{
	display:none;
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

        <?php modal('/ask/view', 'main'); ?>

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
</script>
