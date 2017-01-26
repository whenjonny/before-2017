<div class="login-container">
	<div class="hero-unit">
		<div class="login-header">
			<h1>注册新用户</h1>
		</div>
		<form action="{{ url('user/register') }}" method="POST" id="form_register">
			<input type="hidden" name="<?php echo $this->security->getTokenKey() ?>" value="<?php echo $this->security->getToken() ?>"/>
			<div class="login-control">
				<label class="control-label" for="username">账号</label>
				<div class="controls">
					<input type="text" name="username" placeholder="6-16位字母和数字的组合" maxlength="16" value="{{username}}">
				</div>
			</div>
			<div class="login-control">
				<label class="control-label" for="password">密码</label>
				<div class="controls">
					<input type="password" name="password" placeholder="6-16位的数字或字母" value="{{password}}">
				</div>
			</div>
			<div class="login-control">
				<label class="control-label" for="confirm_password">确认密码</label>
				<div class="controls">
					<input type="password" name="confirm_password" placeholder="确认密码" value="{{confirm_password}}">
				</div>
			</div>
			<div class="login-control">
				<label class="control-label" for="email">邮箱</label>
				<div class="controls">
					<input type="text" name="email" placeholder="请输入邮箱" value="{{email}}">
				</div>
			</div>
<!-- 			<div class="login-control">
				<label class="control-label" for="invite_code">验证码</label>
				<div class="controls">
					<input type="text" name="invite_code" placeholder="验证码" class="inviter-code-width">
					<img src="/img/hot.png" alt="" class="verify-head">
				</div>
			</div> -->
			<div class="login-control login-control-style">
				<input type="checkbox"  class="checkbox" name="agreelicense" value="agree" id="cb_agree">我同意
				<a target="_blank" href="/license">《求PS大神服务条款》</a>
			</div>
			<div class="text-center">
				<button class="btn btn-info btn-large register-style" id="register">立即注册</button>
			</div>
		</form>
	</div>
</div>
<script>
	(function(){
		$('#cb_agree').change(function() {
			if($(this).prop('checked'))
				$('#register').show();
			else
				$('#register').hide();
		});
		$('#register').click(function() {
			$('#form_register').submit();
		});
		$('#cb_agree').attr("checked",'true'); 
	})();
</script>

