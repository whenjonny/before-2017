<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>角色</li>
</ul>
<div>
    <input class="form-filter" name="username"/>
    <button id="search" >搜索</button>
</div>

<table id="role_table" class="table table-bordered table-hover"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#role_table"),
        dataTable: {
            "columns": [
                { data: "uid", name: "#" },
                { data: "userurl", name: "头像" },
                { data: "username", name: "用户名"},
                { data: "nickname", name: "昵称"},
                { data: "int_value", name: "用户个人数据" },
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/user/list_roles"
            }
        },

        success: function(data){
            $(".edit").click(function(){
                toastr['success']("标题", "内容");
            });
        },
	});
});
</script>

</div>
