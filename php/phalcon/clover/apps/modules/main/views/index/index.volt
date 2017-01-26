<div class="content">
	<div class="image">
		<span class="image-width">
			<img src="/img/back.png" alt="">
			<span class="img-footer"></span>
			<span class="content-font">求大神帮我P帅点/玩新</span>
		</span>
		<div class="god-title">最热门P图</div>
		<div class="list-img">
		{% for reply in replies %}
		<!-- section -->
			<span class="list-width">
				<a href="{{ url("ask/show/") }}{{reply.ask_id}}#reply{{reply.id}}"><img src="{{reply.image_url}}"  alt=""></a>
				<div class="data">
					<span>赞:{{reply.up_count}}<i></i></span>
					<span>浏览数:<i>{{reply.click_count}}</i></span>
					<span>评论:<i>{{reply.comment_count}}</i></span>
				</div>
				<div class="name-date">
					<span>{{reply.replyer.nickname}}</span>
					<span>{{reply.create_time|time_in_ago}}</span>
				</div>
			</span>
		<!-- section -->
		{% endfor %}
		</div>


	</div>
	<div class="content-right">
		<span>
			国内首个图片处理|社交平台 集齐大神智慧,满足大众需求 一千个用户,一千个PS大神。 快来收听我们的微博吧。每天 奉上大神神作!!
		</span>
		<img src="/img/box-shadow.png" class="box-shadow"  alt="">
		<span class="border">
			<span class="image-right">
				<img src="/img/code.png"  alt="">
				<img src="/img/font.png" class="fload-font" alt="">
			</span>
			<span class="font-generalize">全球首个微信P图应用，方便快 捷，有什么理由不扫一扫  </span>
		</span>
		<img src="/img/box-shadow.png" class="box-shadow"  alt="">
	</div>
</div>