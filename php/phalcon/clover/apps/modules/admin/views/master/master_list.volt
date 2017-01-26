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
      <li class="master_type all active">
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

<?php modal('/master/recommend'); ?>

<table id="master_list" class="table table-bordered table-hover"></table>

<script>
var table = null;
$(function(){
    table = new Datatable();
    table.init({
        src: $("#master_list"),
        dataTable: {
            "columns": [
                { data: "uid", name: "账号ID" },
                { data: "username", name:"用户名"},
                { data: "nickname", name: "昵称"},
                { data: "sex", name:"性别"},
                { data: "asks_count", name:"求P数"},
                { data: "replies_count", name:"作品数"},
                { data: "total_inform_count", name: "被举报数"},
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/master/list_masters"
            }
        },
        success:function(){}
    });

    $('#master_list').on('click', '.recommend', function(){
        var tr = $(this).parents('tr');
    	var master_id = tr.find('.db_uid').text();
    	var master_name = tr.find('.db_nickname').text();

        var dialogBox = $('#recommend');
        dialogBox.find('input[name="user_name"]').val(master_name);
        dialogBox.find('input[name="user_id"]').val(master_id);
    });
});
</script>
</div>
