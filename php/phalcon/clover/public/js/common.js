$(function() {
	// 显示提醒信息
	setTimeout(function(){
		$('.alert').slideDown('slow', function() {
			setTimeout(function(){
				$('.alert').slideUp('slow'); 
			}, 3000); 
		});
	}, 1000);
});

(function($){
	var psgod = psgod || {};

	psgod.login = function() {
		var username = $('input[name=username]').val();
		var password = $('input[name=password]').val()

		if (username == ''){
			alert('用户名不能为空');
			return;
		}

		if (password == ''){
			alert('密码不能为空');
			return;
		}

		$.ajax({
			url : '/user/login',
			type: 'post',
			dataType: 'json',
			data: {
				username : username,
				password : password
			},
			success : function(data) {
				if (data.ret) {
					location.href = '/ask/hot';
				}
				else{
					alert(data.info);
				}
			}
		});
	}

	window.psgod = psgod;
})($);

function check_login(){
	if($("#_uid").val() == ""){
		alert("请先登录");
		return false;
	}
}