<div class="section-wrok dl-padding reply" data-id="{{__replyInfo['id']}}" data-praised="{{__replyInfo['uped']}}">
	<div class="mc-head">
		<a href="/user/profile/{{__replyInfo['uid']}}">
		<span class="ranking-sex">
			<img src="{{__replyInfo['avatar']}}" alt="">
			<span class="sex bc-{% if __replyInfo['sex'] == 1 %}blue{% else %}pink{% endif %} icon-{% if __replyInfo['sex'] == 1 %}boy{% else %}girl{% endif %}"></span>
		</span>
		<span class="mc-name">{{__replyInfo['nickname']}}</span>
		</a>
		<span class="mc-time">{{__replyInfo['create_time']|time_in_ago}}</span>
		<span class="img-load download_reply" data="{{__replyInfo['id']}}">
		  <button class="download-picture">下载作品</button>
		</span>
	</div>
	<div class="border-lrb">
        <div class="picture-hot">
            <?php
				$__replyInfo['image_url'] = watermark2( $__replyInfo['image_url'] );
				echo get_image_labels($__replyInfo, 398, 533);
            ?>
		</div>
		<div class="detail-value">
			<div class="share-friend hover-weixin-code border-right-color" data-action="wxshare">
				<i class="detail-bubble icon-bubble"></i>
				<span class="bubble-amount amount-margin-left"><?php echo $__replyInfo['weixin_share_count']; ?></span>
				<div class="weixin-code">
					<img src="/img/code.jpg" alt="微信分享二维码">
				</div>
			</div>
			<div class="share-friend border-right-color{% if __replyInfo['uped'] == 1 %} click-color{% endif %}" data-action="praise">
				<i class="detail-praise icon-assist"></i>
				<span class="praise-amount"><?php echo $__replyInfo['up_count']; ?></span>
			</div>
			<div class="share-friend border-right-color comment-amount-btn">
				<i class="speech-bubble icon-comment"></i>
                <span class="bubble-amount"><?php echo $__replyInfo['comment_count']; ?></span>
			</div>
			<div class="share-friend border-none" data-action="others">
				<span class="icon-ellipsis"></span>
				<div class="ellipsis-share">
					<div class="wb-share">
					    <wb:share-button appkey="1251119895" addition="number" type="button" default_text="<?php if($rtext)echo $rtext; else echo '@求PS大神app ';?>" pic="<?php echo $rpic; ?>" picture_search="false"></wb:share-button>
					</div>
					<div class="es-report inform-btn">
						<a href="#">
							<i class="icon-report"></i>
							<span class="font-report">举报</span>
						</a>
					</div>
				</div>
			</div>

		</div>

		<?php $__dealReply = $__replyInfo;  ?>
		{% include "comment/commentArea.volt" %}
	</div>
</div>
