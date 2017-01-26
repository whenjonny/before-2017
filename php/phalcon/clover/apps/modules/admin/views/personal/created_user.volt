<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建用户列表</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="账号ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="角色名称">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="展示名称">
    </div>

    <div class="form-group">
        <input name="phone" class="form-filter form-control" placeholder="手机号码">
    </div>
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<table class="table table-bordered table-hover" id="list_users_ajax"></table>

<script>
var table = null;
$(function() {
    table = new Datatable();
    table.init({
        src: $("#list_users_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "账号ID" },
                { data: "creator", name:"后台账号"},
                { data: "create_time", name:"创建时间"},
                { data: "phone", name:"手机号"},
                { data: "nickname", name: "昵称"},
                { data: "avatar", name:"头像"},
                { data: "username", name:"用户名"},
            ],
            "ajax": {
                "url": "/personal/list_created_users"
            }
        },
        success: function(){}
    });
});
</script>

