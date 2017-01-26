<div class="comment-detail">
<!-- 头部 -->
	<div class="comment-frame">
		<div class="comment-head">
			<div class="comment-center head-portrait">
			  <div class="pf_photo">
				<span class="ranking-sex">
					<img src="/img/avatar.jpg" alt="">
				</span>
			  </div>
			</div>
			<div class="name-center">
                转身说爱你
                <i class="sex-head bc-pink icon-girl"></i>
                <span class="profile-count-score">待结算:5 已结算:</span>
            </div>
            <div class="pf_opt">
            	<div class="opt_box">
            		<div class="opt_box_1">
            			<a href="" class="W_btn_c btn_34px">+ 关注</a>
            		</div>
            		<div class="opt_box_1">
            			<a href="" class="W_btn_d btn_34px">私聊</a>
            		</div>
            	</div>
            </div>		
         </div>
         <div class="PCD_tab S_bg2">
         	<div class="tab_wrap">
         		<table>
         			<tbody>
         				<tr>
         					<td class="">
         						<a class="tab_link" href="">
         							<i class="icon-camera"></i>
         							<span class="S_txt1 t_link">求助</span>
         							<span class="ani_border"></span>
         						</a>
         					</td>
         					<td class="">
         						<a class="tab_link" href="" >
         							<i class="icon-underway"></i>
         							<span class="S_txt1 t_link">进行中</span>
         							<span class="ani_border"></span>
         						</a>
         					</td>
         					<td class="">
         						<a class="tab_link" href="" >
         							<i class="icon-work"></i>
         							<span class="S_txt1 t_link">作品</span>
         							<span class="ani_border"></span>
         						</a>
         					</td>
         					<td class="">
         						<a class="tab_link" href="" >
         							<i class="icon-star-full"></i>
         							<span class="S_txt1 t_link">收藏</span>
         							<span class="ani_border"></span>
         						</a>
         					</td>
         				</tr>
         			</tbody>
         		</table>
         	</div>
         </div>
	</div>

<div class="detail">
	<div class="dl-content">
		<div class="section-list">
		{% for reply in replies %}
			<div class="section-wrok dl-padding" id="reply{{reply['id']}}">
				<div class="mc-head">
					<a href="/user/profile/{{reply['uid']}}">
					<span class="ranking-sex">
						<img src="{{reply['avatar']}}" alt="">
						<span class="sex bc-{% if reply['sex'] == 1 %}blue{% else %}pink{% endif %} icon-{% if reply['sex'] == 1 %}boy{% else %}girl{% endif %}"></span>
					</span>
					<span class="mc-name">{{reply['nickname']}}</span>
					</a>
					<span class="mc-time">{{reply['create_time']|time_in_ago}}</span>
					<span class="img-load download_reply" data="{{reply['id']}}"><img src="/img/load.png" alt=""></span>
				</div>
				<div class="border-lrb">
                    <div class="picture-hot">
                        <?php echo get_image_labels($reply, 398, 533); ?>
					</div>
					<div class="detail-value">
						<div class="share-friend hover-weixin-code border-right-color">
							<i class="detail-bubble icon-bubble"></i>
							<span class="bubble-amount amount-margin-left">123</span>
							<div class="weixin-code">
								<img src="/img/code.jpg" alt="">
							</div>
						</div>
						<div class="share-friend click-color border-right-color">
							<i class="detail-praise icon-praise"></i>
							<span class="praise-amount">123</span>
						</div>
						<div class="share-friend border-right-color">
							<i class="speech-bubble icon-speech-bubble"></i>
							<span class="bubble-amount">123</span>
						</div>
						<div class="share-friend border-none">
							<span class="icon-ellipsis"></span>
						</div>
					</div>
					<!-- 评论列表 -->
					<div class="comment-list">
						<div class="comment-padding">
						<!-- 评论对话框 -->
						<div class="comment-border-head">
							<div>
								<div class="comment-portrait">
									<img src="/img/avatar.jpg" alt="">
								</div>
								<div class="comment-publish">
									<div class="p-input">
										<textarea name="" class="w_input click-border-color"></textarea>
									</div>
								</div>
							</div>
							<div class="comment-button-height">
								<div class="comment-button">
									<a href="" class="W_btn_a W_btn_a_disable" >
										评论
									</a>
								</div>
							</div>
						</div>
							<!-- 热门评论 -->
						<div class="S_line1 comment-border-head">
							<div class="comment-list-section">
								<div class="hot-comment-title hot-comment-color">热门评论</div>
								<div class="">
									<div class="comment-portrait">
										<img src="/img/avatar.jpg" alt="">
									</div>
								</div>
								<div class="comment-name-message">
									<div class="comment-name"><a href="">刘金平:</a>一年了 你们又回到启航的地方 没有了一年前的稚嫩 多了几分成熟 台风越来越稳.</div>
									<div class="comment-time-section">
										<div class="comment-reply">
											<ul class="clearfix">
												<li class="comment-reply-button">
													<span class="comment-line S_line1">
														<a href="">回复</a>
													</span>
												</li>
												<li>
													<span class="line S_line1 cursor click-color1">
														<i class="icon-praise"></i>
														<em>111</em>	
													</span>
												</li>
											</ul>
										</div>
										<div class="comment-time">4月11日 23:14 </div>
									</div>
									<!-- 回复评论 -->
									<div class="WB_repeat_in S_bg2">
										<div class="WB_feed_publish clearfix">
											<div class="WB_publish">
												<div class="p_input">
													<textarea class="W_input click-border-color"></textarea>
												</div>
												<div class="comment-button-height">
													<div class="comment-button">
														<a href="" class="W_btn_a W_btn_a_disable">
															评论
														</a>
													</div>
												</div>
											</div>
											
										</div>
									</div>
								</div>
							</div>
						</div>
							<!-- 最新评论 -->
						<div class="comment-border-head">
							<div class="comment-list-section">
								<div class="hot-comment-title newest-comment-color">最新评论</div>
								<div class="">
									<div class="comment-portrait">
										<img src="/img/avatar.jpg" alt="">
									</div>
								</div>
								<div class="comment-name-message">
									<div class="comment-name"><a href="">刘金平:</a>一年了 你们又回到启航的地方 没有了一年前的稚嫩 多了几分成熟 台风越来越稳.</div>
									<div class="comment-time-section">
										<div class="comment-reply">
											<ul class="clearfix">
												<li class="comment-reply-button">
													<span class="comment-line S_line1">
														<a href="">回复</a>
													</span>
												</li>
												<li>
													<span class="line S_line1 cursor click-color1">
														<i class="icon-praise"></i>
														<em>111</em>	
													</span>
												</li>
											</ul>
										</div>
										<div class="comment-time">4月11日 23:14 </div>
									</div>
<!-- 									<div class="WB_repeat_in S_bg2">
										<div class="WB_feed_publish clearfix">
											<div class="WB_publish">
												<div class="p_input">
													<textarea class="W_input"></textarea>
												</div>
												<div class="comment-button-height">
													<div class="comment-button">
														<a href="" class="W_btn_a W_btn_a_disable">
															评论
														</a>
													</div>
												</div>
											</div>
											
										</div>
									</div> -->
								</div>
							</div>			
						</div>
						</div>

					</div>


				</div>
			</div>
		{% endfor %}
		</div>
	  <div class="WB_cardwrap S_bg2">
		<!-- 关注 粉丝 求P 作品 -->
		<div class="PCD_counter">
			<div class="WB_innerwrap">
				<table class="tb_counter">
					<tbody>
						<tr>
							<td class="S_line1">
							   <a href="">
							      <span class="S_txt2 hover">关注</span>
							      <strong class="W_f12 hover">123</strong>
							    </a>
							</td>
							<td class="S_line1">
							   <a href="">
							      <span class="S_txt2 hover">粉丝</span>
							      <strong class="W_f12 hover">123132</strong>
							    </a>
							</td>
							<td class="S_line1">
							   <a href="">
							      <span class="S_txt2 hover">求P</span>
							      <strong class="W_f12 hover">12313</strong>
							    </a>
							</td>
							<td class="S_line1">
							   <a href="">
							      <span class="S_txt2 hover">作品</span>
							      <strong class="W_f12 hover">12313</strong>
							    </a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
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

</div>

