<ul class="breadcrumb">
  <li>
    <a href="#">系统模块</a>
  </li>
  <li>推荐App</li>
  <div class="btn-group pull-right">
        <a href="#add_app" data-toggle="modal"  data-target="#add_app" class="add">添加App</a>
    </div>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="app_name" class="form-filter form-control" placeholder="应用名称">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search">搜索</button>
    </div>
</div>

<table id="app_list" class="table table-bordered table-hover"></table>

<?php modal('/app/add_app'); ?>

<script>
var table = null;
$(function(){
	table = new Datatable();
    table.init({
        src: $("#app_list"),
        dataTable: {
            "columns": [
            	{ data: "id", name:"ID"},
                { data: "logo", name: "图标" },
                { data: "app_name", name: "应用名称" },
                { data: "create_time", name: "添加时间" },
                { data: "oper", name: "操作" }
            ],
            "ajax": {
                "url": "/app/list_apps"
            },
            'ordering':false
        },
        success:function(){}
    });

    $('#app_list').on('click', '.delete', function(){
    	var app_id = $(this).parents('tr').find('.db_id').text();
    	$.post('/app/del_app', {'app_id':app_id},function(result){
    		if(result.ret==1){
    			toastr['success']('删除成功');
                table.submitFilter();  //刷新表格
    		}
    	});
    });


    $( "#app_list tbody" ).sortable({
      placeholder: "app-item-highlight",
      update: function(){
      	var sorts = [];
      	var items = $('#app_list tr td.db_id');
      	items.each(function(i){
      		sorts.push(items[i].innerText);
      	});

      	$.post('/app/sort_apps',{'sorts':sorts.join(',')},function(result){
      		if( result.ret==1){
      			toastr['success']('排序更改成功');
      			$('#add_app').modal('hide');
                table.submitFilter();  //刷新表格
      		}
      	})
      }
    });
    $( "#app_list tr" ).disableSelection();
});
</script>
<script src="/theme/assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/theme/assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.css"/>

<style>
.applogo{
	height: 50px;
	width: 50px;
	border-radius: 12px !important;
	border: 1px solid lightgray;
}
.app-item-highlight{
	height: 67px;
	background-color: khaki;
}
</style>
