<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>审核作品</li>
</ul>
<div class="btn-group pull-right">
    <ul class="dropdown-menu pull-right" role="menu">
    <li>
    <a href="#">Action</a>
    </li></ul>
</div>

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
      <li>
        <a href="/check/wait">
          待审核 </a>
      </li>
      <li class="active">
        <a href="/check/pass">
         审核通过 </a>
      </li>
      <li>
        <a href="/check/reject">
          审核拒绝</a>
      </li>
<!--
      <li>
        <a href="/check/release">
          已发布</a>
      </li>
-->
</div>

<table class="table table-bordered table-hover" id="check_ajax"></table>

<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#check_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "ID" },
                { data: "score", name: "分数"},
                { data: "username", name: "姓名" },
                { data: "created", name:"发布时间"},
                { data: "thumb_url", name:"作品内容"},
                { data: "desc", name:"描述"}
            ],
            "ajax": {
                "url": "list_replies?status=1"
            }
        },
        success: function(data){
            $(".pass").click(function(){

            });
        }
    });
});
</script>
