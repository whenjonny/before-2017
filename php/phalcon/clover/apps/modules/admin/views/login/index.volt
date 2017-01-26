<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户登录</title>
<link href="/css/login/login.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery-1.11.2.min.js"></script>
</head>

<body class="loginbody">
<div class="login-screen">
	<div class="login-icon">LOGO</div>
    <div class="login-form">
        <h1>系统管理登录</h1>
        <div class="control-group">
            <input type="text" id="txtUserName" class="login-field" placeholder="用户名" title="用户名" autofocus/>
            <label class="login-field-icon user" for="txtUserName"></label>
        </div>
        <div class="control-group">
            <input type="password" id="txtPassword" class="login-field" placeholder="密码" title="密码" />
            <label class="login-field-icon pwd" for="txtPassword"></label>
        </div>
        <!-- <div class="control-group">
            <input name="valid_code" id="txtValidCode" type="text" class="login-field fl w45p" placeholder="验证码" title="验证码"/>
            <img class="fl w45p ml15 validate" id="txtValidCodeImg" src="#" alt="验证码"/>
        </div> -->
        <div><input type="submit" name="btnSubmit" value="登 录" id="btnSubmit" class="btn-login" onclick="ajaxLogin()" /></div>
        <span class="login-tips"><i></i><b id="msgtip">请输入用户名和密码</b></span>
    </div>
    <i class="arrow">箭头</i>
</div>
<script>

    $('input').keydown(function(e){
        var e = e || event;
        if (e.keyCode == 13){
            ajaxLogin();
        }
    })

    function ajaxLogin(){
        $.ajax({
            type:'POST',
            url :'/Login/index',
            dataType:'json',
            data:{
                username  : $('#txtUserName').val(), 
                password   : $('#txtPassword').val()
                // valid_code : $('#txtValidCode').val()
            },
            success: function(data){
                if (data.ret == 0){
                    location.href=''+data.data.url+'';
                }else if (data.ret == 1) {
                    alert(data.info);
                    $('#txtUserName').focus();
                    return false;
                }else if (data.ret == 2){
                    alert(data.info);
                    $('#txtPassword').focus();
                    return false;
                }else{
                    alert(data.info);
                    return false;
                }
                    // $('#txtValidCode').focus().val('');
                    // $('.validate').prop('src','');
            }
        })
    }
</script>
</body>
</html>