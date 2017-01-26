<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>
    <a href="/waistcoat/parttime">运营账号</a>
  </li>
  <li>结算记录</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="operate_to" class="form-filter form-control" placeholder="账号ID">
    </div>
    <!--
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="角色名称">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="展示名称">
    </div>
    -->
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<table class="table table-bordered table-hover" id="list_scores_ajax"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#list_scores_ajax"),
        dataTable: {
            "columns": [
                { data: "id", name: "结算ID" },
                //{ data: "operate_type", name:"操作类型"},
                { data: "operate_to", name:"兼职账号"},
                { data: "score", name:"分数"},
                //{ data: "data", name:"操作前|操作后"},
                { data: "username", name:"结算员"},
                { data: "create_time", name:"结算时间"}
            ],
            "ajax": {
                "url": "/score/list_scores"
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

