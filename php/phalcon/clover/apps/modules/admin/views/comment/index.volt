<ul class="breadcrumb">
  <li>
    <a href="#">评论模块</a>
  </li>
  <li>创建评论</li>
</ul>
<div>
<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="用户ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="名称">
    </div>
    <div class="form-group">
        <input name="content" class="form-filter form-control" placeholder="内容">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search">搜索</button>
    </div>
</div>

<table class="table table-bordered table-hover" id="datatable_ajax"></table>

<script>
var table = null;
jQuery(document).ready(function() {
    var table = new Datatable();
    table.init({
        src: $("#datatable_ajax"),
        dataTable: {
            "columns": [
                { data: "id", name: "#" },
                { data: "uid", name: "评论用户" },
                { data: "target_id",name:"评论的帖子的ID"},
                { data: "content", name: "评论内容" },
                { data: "type", name: "评论类型"},
                { data: "create_time", name: "评论时间"},
                { data: "status", name: "评论状态"},
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/comment/list_comments"
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
