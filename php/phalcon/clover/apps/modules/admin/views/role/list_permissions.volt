<ul class="breadcrumb">
    <li>
        <a href="#">用户模块</a>
    </li>
    <li>权限模块管理</li>
    <div class="btn-group pull-right">
        <a href="#add_permission" data-toggle="modal"> 添加模块</a>
    </div>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="pid" class="form-filter form-control" placeholder="角色ID">
    </div>
    <div class="form-group">
        <input name="display_name" class="form-filter form-control" placeholder="展示名称">
    </div>
    <div class="form-group">
        <input name="controller_name" class="form-filter form-control" placeholder="Controller名称">
    </div>
    <div class="form-group">
        <input name="action_name" class="form-filter form-control" placeholder="Action名称">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search">搜索</button>
    </div>
</div>

<table id="permission_table" class="table table-bordered table-hover"></table>

<?php modal('role/add_permission'); ?>
<?php modal('role/edit_permission'); ?>
<?php modal('role/delete_permission'); ?>

<script>
var table = null;
$(function(){  
    table = new Datatable();
    table.init({
        src: $("#permission_table"), 
        dataTable: { 
            "columns": [
                { data: "id", name: "#" },
                { data: "display_name", name: "模块名称" },
                { data: "controller_name", name: "controller名" },
                { data: "action_name", name: "action名"},
                { data: "create_time", name: "创建时间"},
                { data: "update_time", name: "更新时间" },
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/role/list_permissions"
            }
        },
        success:function(){}
    });
});
</script>
</div>
