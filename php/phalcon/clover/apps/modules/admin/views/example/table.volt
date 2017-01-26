<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建账号</li>
</ul>
<div>
<input class="form-filter" name="username"/>
<button id="search" >搜索</button>
<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li>
        <a href="/example/table?type=1">
          求助账号 </a>
      </li>
      <li class="active">
        <a href="/example/table2">
        作品账号 </a>
         </li>
      <li>
        <a href="/example/table3">
          兼职账号 </a>
      </li>
    </ul>
</div>
<table class="table table-bordered table-hover" id="datatable_ajax"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#datatable_ajax"),
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
                "url": "/example/list_users"
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
