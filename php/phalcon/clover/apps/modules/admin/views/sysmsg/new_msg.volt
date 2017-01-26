<ul class="breadcrumb">
  <li>
	<a href="#">消息管理</a>
  </li>
  <li>系统消息</li>
  <li>新建消息</li>
</ul>
<div class="tabbable-line">
	<ul class="nav nav-tabs">
	  <li class="active">
		<a href="/sysmsg/new_msg">
		新消息</a>
	  </li>
	  <li>
		<a href="/sysmsg/msg_list?type=pending">
		待发布</a>
	  </li>
	  <li>
		<a href="/sysmsg/msg_list?type=sent">
		已发布</a>
	  </li>
	  <li>
		<a href="/sysmsg/msg_list?type=deleted">
		已删除</a>
	  </li>
	</ul>
</div>


<form class="" style="width: 40%;" name="new_msg" method="post" action="##">
	<fieldset>
		<div id="legend" class="">
			<legend class="">新建系统消息</legend>
		</div>

		<div class="form-group">
			<!-- Select Basic -->
			<label class="">消息类型</label>
			<div class="controls">
				<select class="form-control" name="msg_type">
					<option value="1">通知</option>
					<option value="2">活动</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<!-- Text input-->
			<label class="" for="input01">标题</label>
			<div class="controls">
				<input type="text" class="form-control" name="title">
			</div>
		</div>

		<div class="form-group">
			<!-- Select Basic -->
			<label class="">类型</label>
			<div class="controls">
				<select class="form-control" name="target_type">
					<option value="1">求助</option>
<!-- 					<option value="2">作品</option>
					<option value="3">评论</option> -->
					<option value="4">用户</option>
					<option value="0">跳转URL</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<!-- Text input-->
			<label class="" for="input01">类型ID</label>
			<div class="controls">
				<input type="text" name="target_id" class="form-control">
				<p class="help-block">类型相关的ID</p>
			</div>
		</div>

		<div class="form-group">
			<!-- Prepended checkbox -->
			<label class="">跳转URL</label>
			<div class="controls">
				<input class="form-control" name="jump_url" type="text" placeholder="URL">
				<p class="help-block"></p>
			</div>
		</div>


		<div class="form-group">
			<label>配图（可选）</label>
			<div class="controls">
				<img id="logo_preview" class="img-display" />
				<input type="hidden" name="pic_url"/>
				<input type="file" id="logo_upload" class="btn blue" value="上传图片" />
			</div>
		</div>

		<div class="form-group">
			<!-- Search input-->
			<label class="">发送时间</label>
			<div class="controls">
				<input type="text" name="post_time" class="form-control search-query">
				<p class="help-block"></p>
			</div>
		</div>

		<div class="form-group">
			<!-- Search input-->
			<label class="">接受者</label>
			<div class="controls">
				<input type="text" placeholder="输入用户名" name="receiver_uids" class="form-control search-query">
				<p class="help-block">群发请留空</p>
			</div>
		</div>

		<div class="form-group">
			<label class=""></label>

			<!-- Button -->
			<div class="controls">
				<button class="btn btn-info post_form">发布</button>

				<input type="checkbox" name="send_as_system" id="send_as_system" checked="checked" style="display:none;"/>
				<label for="send_as_system" style="display:none;">以系统身份发送</label>
			</div>
		</div>
	</fieldset>
  </form>
<script>
	var dtpickerOption = {
		format: 'yyyy-mm-dd hh:ii',
		autoclose: true
	}

	function loadLogo(data){
		Common.preview('logo_preview', data);
		$('#logo_preview').attr('data-id', data.data.id);
		$("input[name='pic_url']").val(data.data.url);
	}

	$(function(){
		Common.upload('#logo_upload',loadLogo, null, {url:'/image/upload'});

		$('input[name="post_time"]').datetimepicker(dtpickerOption);
		$('select[name="target_type"]').on('change', function(){
			var urlBox = $('input[name="target_id"]');
			if( $(this).val() === '0' ){
				urlBox.removeProp('required');
				urlBox.attr('disabled', true);
				urlBox.prop('readonly');
			}
			else{
				urlBox.prop('required');
				urlBox.attr('disabled',false);
				urlBox.removeProp('readonly');
			}
		});

		$('button.post_form').on('click', function(e){
			e.preventDefault();var uids=Array();

			$('input[name="receiver_uids"]').siblings('ul').find('li.receiver_uids').each(function(){
				uids.push($(this).attr('data-id'));
			});
			uids = uids.join(',');

			$('input[name="receiver_uids"]').val(uids);
			$.post('/sysmsg/post_msg', $('form[name="new_msg"]').serialize(), function( data ){
				alert(data.data);
				if( data.ret ){
					location.href="/sysmsg/msg_list?type=pending";
				}
			})
		});

		$('input[name="receiver_uids"]').tokenInput("/sysmsg/getUserList",{
			propertyToSearch: 'username',
			jsonContainer: 'data',
			theme: "facebook",
			// preventDuplicates: true,
			//tokenValue: 'data-id',
			resultsFormatter: function(item){
				var genderColor = item.sex == 1 ? 'deepskyblue' : 'hotpink';
				return "<li>" +
				"<img src='" + item.avatar + "' title='" + item.username + " " + item.nickname + "' height='25px' width='25px' />"+
				"<div style='display: inline-block; padding-left: 10px;'>"+
					"<div class='username' style='color:"+genderColor+"'>" + item.username + "</div>"+
					"<div class='nickname'>" + item.nickname + "</div>"+
				"</div>"+
				"</li>" },
			tokenFormatter: function(item) {
				return "<li class='token-input-token-facebook receiver_uids' data-id='"+item.uid+"'>" +
				"<a href='/user/profile/"+item.uid+"'>"+item.username + "</a>-" +
				item.nickname + "("+item.uid+')'+"</li>";
			},
		});

	});
</script>
<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
<style>
#logo_preview{
	height: 50px;
	width:50px;
	border-radius: 12px !important;
	border: 1px solid lightgray;
}
.uploadify {
	position: absolute;
	right: 0px;
	top: 50%;
}
</style>
