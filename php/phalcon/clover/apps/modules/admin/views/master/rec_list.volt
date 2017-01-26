<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>推荐大神</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="账号ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="用户名">
    </div>
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>


<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="master_type all">
        <a href="/master/master_list">
        大神列表</a>
      </li>
      <li class="master_type pending">
        <a href="/master/rec_list?status=pending#pending">
        待生效</a>
      </li>
      <li class="master_type valid">
        <a href="/master/rec_list?status=valid#valid">
        生效中</a>
      </li>
    </ul>
</div>

<table id="master_list" class="table table-bordered table-hover"></table>

<script>
var table = null;
$(function(){
	var hash = location.hash;
	var status = 'pending';
	if(hash){
		status = hash.substr(1);
	}
	$('li.master_type.'+status).addClass('active');


    table = new Datatable();
    table.init({
        src: $("#master_list"),
        dataTable: {
            "columns": [
                { data: 'id', name: 'ID'},
                { data: "uid", name: "账号ID" },
                { data: "username", name:"用户名"},
                { data: "nickname", name: "昵称"},
                { data: "sex", name:"性别"},
                { data: 'start_time', name:"生效时间"},
                { data: 'end_time', name:"失效时间"},
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/master/list_recs",
                'data': function(d){
                    d.status=(status=='pending')?0:1;
                }
            }
        },
        success:function(){}
    });


    $('#master_list').on('click', '.recommend', function(){
    	var master_id = $(this).parents('tr').find('.db_id').text();
    	$.post('/master/recommend', {'master_id':master_id},function(result){
    		if(result.ret==1){
    			toastr['success']('状态更新成功');
                table.submitFilter();  //刷新表格
    		}
    	});
    });

    $('#master_list').on('click', '.cancel', function(){
        var master_id = $(this).parents('tr').find('.db_id').text();
        $.post('/master/cancel', {'id':master_id},function(result){
            if(result.ret==1){
                toastr['success']('取消成功');
                table.submitFilter();  //刷新表格
            }
        });
    });
});
</script>
</div>
