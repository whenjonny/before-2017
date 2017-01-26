<div class="container">
<div class="content-hot">
	{% for ask in asks %}
	<div class="section-list">
		<div class="section-wrok">
			<div class="mc-head padding-hot">
				<input hidden="hidden" value="{{ask['is_download']}}" />
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
				<span class="mc-time">{{ask['update_time']|time_in_ago}}</span>
			</div>
			<div class="picture-hot">
                <?php echo get_image_labels($ask, 300, 400, false); ?>
			</div>
			<div class="footer-portrait bc-bg">
				<div class="hidden-portrait">
					{% for psgod in ask['psgods'] %}
					<a href="/user/profile/{{psgod['uid']}}" title="{{psgod['nickname']}}">
						<img class="deity-portrait" src="{{psgod['avatar']}}">
					</a>
					{% endfor %}
					<button class="br-radius dy-production center">{{ask['reply_count']}}</button>
				</div>
			</div>
		</div>
	</div>
	{% endfor %}
	{{page}}
	<br>
</div>
</div>
