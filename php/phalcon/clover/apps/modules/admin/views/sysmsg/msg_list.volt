<ul class="breadcrumb">
  <li>
    <a href="#">消息管理</a>
  </li>
  <li>系统消息</li>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="title" class="form-filter form-control" placeholder="标题">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search">搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li>
        <a href="/sysmsg/new_msg">
        新消息</a>
      </li>
      <li class="pending">
        <a href="/sysmsg/msg_list?type=pending">
        待发布</a>
      </li>
      <li class="sent">
        <a href="/sysmsg/msg_list?type=sent">
        已发布</a>
      </li>
      <li class="deleted">
        <a href="/sysmsg/msg_list?type=deleted">
        已删除</a>
      </li>
    </ul>
</div>

<table class="table table-bordered table-hover" id="sysmsg_list"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    var query_type = getQueryVariable('type');
    $('li.'+query_type).addClass('active');

    table = new Datatable();
    table.init({
        src: $("#sysmsg_list"),
        dataTable: {
            "columns": [
                { data: "id", name: "系统消息ID" },
                { data: "msg_type", name: "消息类型" },
                { data: "title", name:"标题"},
                { data: "jump", name:"跳转链接"},
                { data: "receivers", name:"接收者"},
                { data: "post_time", name:"发送时间"},
                { data: "create_time", name:"创建时间"},
                { data: "create_by", name:"发布者"},
                { data: "oper", name:"操作"}
            ],
            "ajax": {
                "url": "/sysmsg/get_msg_list?type="+query_type
            }
        },

        success: function(data){
                $("a.del_msg").click(function(){
                var target_id   = $(this).attr("data");
                var type        = $(this).attr("type");
                if(confirm("确认删除此系统消息?")){
                    $.post("/sysmsg/del_msg", {
                        id: target_id,
                        type: type
                    }, function(){
                        toastr['success']("删除成功");
                        table.submitFilter();
                    });
                }
            });
        },
  });
});
</script>
