<ul class="breadcrumb">
    <li>
        <a href="#">用户模块</a>
    </li>
    <li>角色</li>
    <div class="btn-group pull-right">
        <a href="#add_role" data-toggle="modal" class="add">创建角色</a>
    </div>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="role_id" class="form-filter form-control" placeholder="ID">
    </div>
    <div class="form-group">
        <input name="role_name" class="form-filter form-control" placeholder="角色名称">
    </div>
    <div class="form-group">
        <input name="role_display_name" class="form-filter form-control" placeholder="展示名称">
    </div>
    <div class="form-group">
        <input name="role_created_beg" class="form-filter form-control" placeholder="开始时间">
        <input name="role_created_end" class="form-filter form-control" placeholder="结束时间">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<table id="role_table" class="table table-bordered table-hover"></table>

<?php modal('/role/add_role'); ?>
<?php modal('/role/set_previlege'); ?>

<script>
var table = null;
$(function() {    
    table = new Datatable();
    table.init({
        src: $("#role_table"), 
        dataTable: { 
            "columns": [
                { data: "id", name: "#" },
                { data: "name", name: "角色名称" },
                { data: "display_name", name: "展示名称"},
                { data: "create_time", name: "创建时间"},
                { data: "update_time", name: "更新时间" },
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/role/list_roles"
            }
        },

        success: function(){},
    });

    $('a.add[href="#add_role"]').on('click',function(){
        $("#add_role form")[0].reset();
        table.submitFilter();
    })
});

</script>
</div>
