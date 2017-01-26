<!DOCTYPE html>
<html lang="zh-CN" xmlns:wb="http://open.weibo.com/wb">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="zh-CN" />
		<meta name="keywords" content="PS,大神,PS大神,photoshop,图片处理,搞笑,搞笑图片,娱乐,求大神,恶搞,美化,恶搞图片" />
		<meta name="description" content="PS、搞笑图片、神图、恶搞、搞笑、图片处理、美化图片、PS图片处理，PS大神，求助P图，腾讯开放平台" />
		<meta name="author" content="clover 团队" />
		<meta name="Copyright" content="求PS大神@2015" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1" />
        <meta property="wb:webmaster" content="04ca39cff6e06384" />
		<script type="text/javascript" src="/js/jquery-1.11.2.min.js"></script>
		<link rel="stylesheet" type="text/css" href="/css/page.css">

		<!-- -->
		<link href="/img/favicon.ico" rel="bookmark" type="image/x-icon" />
		<link href="/img/favicon.ico" rel="icon" type="image/x-icon" />
		<link href="/img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		{{ getTitle() }}
		{{ assets.outputCss() }}
	</head>

	<body>
		{{ flash.output() }}
		<!-- 导航 -->
		<div class="header">
			<div class="width">
				<a class="logo" href='/ask/hot'>
					<img src="/img/logo.png" alt="">
				</a>
				<?php
			        $controller_name = $this->dispatcher->getControllerName();
			        $action_name = $this->dispatcher->getActionName();

					$span = array(
						array('url' => 'ask/hot', 'value' => '热门', 'class' => null),
						array('url' => 'ask/latest', 'value' => '求P', 'class' => null),
						array('url' => 'user/inprogress', 'value' => '进行中', 'class' => null)
                    );

                    // 进行中 登录了才显示
					if (!$_uid) unset($span[2]);

                    foreach ($span as $k => $v) {
						$url = explode('/', $v['url']);
						if ($controller_name == $url[0] && $action_name == $url[1]){
							$span[$k]['class'] = 'current';
						}

						echo '<span class="tab_hover">' . $this->tag->linkTo(array($span[$k]['url'], $span[$k]['value'], 'class' => $span[$k]['class'])) . '</span>';
					}
				?>
				<!-- <span>动态</span> -->
                <!-- <span>{{ link_to('ask/add', '求PS') }}</span> -->
                {% if (_uid) %}
                <div class="message">
                    <a href="/user/profile">
	                    <a class="portrait-absolute" href="{{ url('user/profile') }}">
							<div class="ranking-sex">
								<img src="{{_avatar}}" alt="">
							</div>
							<div class="ni-name  name_hover">{{_nickname}}</div>
						</a>
                    </a>
                    <img src="/img/upload.png" alt="">
                    <a href="javascript:;" onclick="javascript:Common.toggle_modal('add_ask_modal', true);">
                    <div class="name name_hover">
                       求P
                    </div>
                    </a>
                    <!-- <img src="../img/remind.png" alt=""> -->
                    <!-- <div class="name">提醒({{message}})</div> -->
                    <a href="/user/logout" class="name name_hover">退出</a>
                </div>
                {% else %}
                <a href="{{ url('user/register') }}" class="login" tabIndex="4">注册</a>
				<a href="javascript:;" onclick="javascript:psgod.login();" class="login" tabIndex="3">登录</a>
				<input type="password" name="password" class="password" placeholder="密码" tabIndex="2">
                <input type="text" name="username" class="user" placeholder="手机/账号" tabIndex="1">
                {% endif %}
			</div>
		</div>
		<!-- /导航 -->
        <!-- 主体 -->
        {{ content() }}
		<!-- /主体 -->
		<!-- 页脚 -->
			<div class="footer">
			<ul class="inline">
				<li class="vaersion">© 2015 求PS大神</li>
	<!-- 			<li><a href="#" title="关于求ps大神">关于</a></li>
				<li><a href="#">条款</a></li>
				<li><a href="#">联系</a></li> -->
				<li>
					<a href="http://weibo.com/qiupsdashen" target="_blank">微博</a>
					<div class="qiupsdashen-weibo"><wb:follow-button uid="3187482345" type="red_1" width="67" height="24" ></wb:follow-button></div>
				</li>
				<li>

		<!-- 			<a href="#"  >IOS下载</a>
					<a href="#"  >安卓版下载</a> -->
					<a class="weixin-two-dimension" href="#">
					    微信公众号
					    <div class="public-two-dimension">
					    	<img src="/img/code.jpg" alt="">
					    </div>
					 </a>
				</li>
			</ul>
			<div class="footer-coding"><a  target="_blank" href="http://www.miitbeian.gov.cn/">粤ICP备15019015号-1</a></div>
			<br>
        </div>
        <input type="hidden" id="_uid" value="{{_uid}}" />
        <input type="hidden" id="_avatar" value="{{_avatar}}" />
        <input type="hidden" id="_sex" value="{{_sex}}" />
        <input type="hidden" id="_nickname" value="{{_nickname}}" />

        {{ assets.outputJs() }}
        <!-- /页脚 -->

        <!-- 求p蒙版 -->
        <?php modal('/ask/add', 'main'); ?>
        <!-- /求p蒙版 -->
    </body>
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?72fb603387e112de17dc84997602e784";
  var s = document.getElementsByTagName("script")[0];
  s.parentNode.insertBefore(hm, s);
})();
console.log("%c", "padding:120px 300px;line-height:250px;background:url('http://pic2.52pk.com/files/130520/1283568_144659_2471.gif') no-repeat;");console.log("招聘ing~，欢迎加入我们团队，i@qiupsdashen.com")
</script>
<script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js" type="text/javascript" charset="utf-8"></script>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-62030262-1', 'auto');
ga('send', 'pageview');

</script>
</html>
