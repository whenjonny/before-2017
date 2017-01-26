<div class="container">
<div class="content-hot">
	<div class="middle">
		<div class="newest-font">提交图片,让大神们处理</div>
		<div class="center">
            <a class="btn-large" href="javascript:;" onclick="javascript:Common.toggle_modal('add_ask_modal', true);">免费上传图片</a>
		</div>
	</div>
	{% for ask in asks %}
	<div class="section-list">
		<div class="section-wrok">
			<div class="mc-head padding-hot">
				<input hidden="hidden" id="input_download" value="{{ask['is_download']}}" />
				<a class="portrait-absolute" href="/user/profile/{{ask['uid']}}">
				<span class="ranking-sex">
					<img src="{{ask['avatar']}}" alt="">
					{% if ask['sex'] == 1 %}
						<span class="sex bc-blue icon-boy"></span>
					{% else %}
						<span class="sex bc-pink icon-girl"></span>
					{% endif %}
				</span>
                <span class="mc-name">{{ask['nickname']}}</span>
				</a>
                <span class="mc-time">{{ask['create_time']|time_in_ago}}</span>
			</div>
			<div class="picture-hot">
                <?php echo get_image_labels($ask); ?>
			</div>
<!-- 			<div class="footer-portrait bc-bg">
				<div class="hidden-portrait">
					<img class="deity-portrait" src="/img/top.jpg" alt="头像">
					<button class="br-radius dy-production center">{{ask['reply_count']}}</button>
				</div>
			</div> -->
		</div>
	</div>
	{% endfor %}

	{{page}}
	<br>
</div>
</div>

<?php modal('/ask/view', 'main'); ?>
