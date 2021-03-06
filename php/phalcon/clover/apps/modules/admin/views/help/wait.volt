
<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>审核作品</li>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="账号ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="名称">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="昵称">
    </div>
    <div class="form-group">
        <input name="role_created_beg" class="form-filter form-control" placeholder="开始时间">
        <input name="role_created_end" class="form-filter form-control" placeholder="结束时间">
    </div>
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="active">
        <a href="/check/wait">
          待审核 </a>
      </li>
      <li>
        <a href="/check/pass">
         审核通过 </a>
      </li>
      <li>
        <a href="/check/reject">
          审核拒绝</a>
      </li>
      <li>
        <a href="/check/release">
          已发布</a>
      </li>
</div>

<table class="table table-bordered table-hover" id="review_ajax"></table>

<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        dom: "<t><'row'<'col-md-5 col-sm-12'li><'col-md-7 col-sm-12'p>>",
        src: $("#review_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "ID" },
                { data: "oper", name: "操作"},
                { data: "parttime_name", name: "姓名" },
                { data: "release_time", name:"发布时间"},
                { data: "ask_image", name:"求助内容"},
                { data: "reply_image", name:"回复内容"}
            ],
            "ajax": {
                "url": "/review/list_reviews?status=4"
            }
        },
        success: function(data){
            $(".edit").click(function(){

            });

            $(".deny").click(function(){
                var obj = {};
                obj.review_id = $(this).attr('data');
                obj.status  = 2;
                obj.data    = "";

                $.post("/review/set_status", obj, function(data){
                    toastr['success']("操作成功");
                    table.submitFilter();
                });
            });


            $(".submit-score").click(function(){
                var obj = {};
                obj.review_id = $(this).attr('data');
                obj.status  = 1;
                obj.data    = $("#review_ajax input[name='score']:checked").val();

                $.post("/review/set_status", obj, function(data){
                    toastr['success']("操作成功");
                    table.submitFilter();
                });
            });
        },
    });
});
</script>
