<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>用户反馈</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="用户ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="名称">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="昵称">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search">搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="fb_type suspend">
        <a href="/feedback/index?type=suspend#suspend">
        待跟进</a>
      </li>
      <li class="fb_type following">
        <a href="/feedback/index?type=following#following">
        解决中</a>
      </li>
      <li class="fb_type all">
        <a href="/feedback/index?type=all#all">
        所有</a>
      </li>
    </ul>
</div>

<table id="feedback_list" class="table table-bordered table-hover"></table>
<style>
    td.db_content{
        max-width: 300px;
        word-wrap: break-word;
        text-align: left;
    }
    ul.opinion_list{
        list-style: none;
        list-style-position: outside;
        padding: 0px;
        min-width: 250px;
        max-width: 500px;
        max-height: 200px;
        overflow: auto;
        text-align: left;
    }
    li.opinion_item{
        margin-bottom: 5px;
    }
    .post_opinion{
        text-align: center;
    }
</style>
<script>
var table = null;
$(function(){
	var hash = location.hash;
	var status = 'suspend';
	if(hash){
		status = hash.substr(1);
	}
	$('li.fb_type.'+status).addClass('active');


    table = new Datatable();
    table.init({
        src: $("#feedback_list"),
        dataTable: {
            "columns": [
                { data: "id", name: "#" },
                { data: "username", name: "反馈者" },
                { data: "sex", name: "用户性别" },
                { data: "avatar", name: "用户头像"},
                { data: "contact", name: "联系方式"},
                { data: "content", name: "反馈内容" },
                { data: "opinion", name:"运营人员记录"},
                { data: "create_time", name: "反馈时间"},
                { data: "crnt_status", name: "状态"},
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/feedback/list_fb?status="+status
            }
        },
        success:function(){}
    });


    $('#feedback_list').on('click', '.chg_status', function(){
    	var status = $(this).attr('data-next-status');
    	var fb_id = $(this).parents('tr').find('.db_id').text();
    	$.post('/feedback/chg_status', {'fb_id':fb_id, 'status':status},function(result){
    		if(result.ret==1){
    			toastr['success']('状态更新成功');
                table.submitFilter();  //刷新表格
    		}
    	});
    });

    $('#feedback_list').on('click','.submit_opinion' ,function(){
        var fbid = $(this).siblings('input[name="fbid"]').val();
        var opinion = $(this).siblings(".opinion").val();
        $.post('/feedback/post_opinion',{'fbid':fbid, 'opinion':opinion}, function( data ){
            if( typeof data.data == 'object' ){
                location.reload();
            }
            else{
                alert(data.data);
            }
        });

    });
});
</script>
