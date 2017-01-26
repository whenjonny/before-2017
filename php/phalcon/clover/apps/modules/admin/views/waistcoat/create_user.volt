<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建账号</li>
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
      <?php
      $arr = explode("/", $_REQUEST['_url']);
      foreach($roles as $role){
          $active = ($arr[2] == $role->role_name)?'active':'';
          echo "<li class='$active' data='".$role->id."'>".
            '<a href="'.$role->role_name.'">'.$role->display_name.'</a>'.
          '</li>';
      }
      ?>
       <a href="#new_topic" data-toggle="modal" class="btn btn-default btn-sm float-right">发布帖子</a>
      <a href="#add_user" data-toggle="modal" class="btn btn-default btn-sm float-right">创建账号</a>
    </ul>
</div>

<table class="table table-bordered table-hover" id="waistcoat_ajax"></table>


<?php modal('/user/add_user'); ?>
<?php modal('/user/remark_user'); ?>

<script>
var table = null;
jQuery(document).ready(function() {
    var role_id = $(".nav li.active").attr("data");

    table = new Datatable();
    table.init({
        src: $("#waistcoat_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "账号ID" },
                { data: "reg_time", name:"创建时间"},
                { data: "sex", name:"性别"},
                { data: "sex", name:"求助数"},
                { data: "sex", name:"作品数"},
                { data: "uped_count", name:"点赞数"},
                { data: "inform_count", name:"举报数"},
                { data: "userurl", name: "头像" },
                { data: "username", name: "用户名"},
                { data: "nickname", name: "昵称"},
                { data: "data", name: "操作"}
            ],
            "ajax": {
                "url": "/waistcoat/list_users?role_id=" + role_id
            }
        },

        success: function(data){
            $(".edit").click(function(){
                var nickname = $(this).attr('nickname');
                $("#remark_user input[name='nickname']").val(nickname);
            });
        },
    });
});
</script>

