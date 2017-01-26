<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建账号</li>
</ul>

<div class="form-inline">
    <div class="form-group">
        <input name="id" class="form-filter form-control" placeholder="ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="账号名">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="昵称">
    </div>
<!--     <div class="form-group">
        <input name="role_created_beg" class="form-filter form-control" placeholder="开始时间">
        <input name="role_created_end" class="form-filter form-control" placeholder="结束时间">
    </div> -->
    <div class="form-group">
    <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <?php
      $arr = explode("/", $_REQUEST['_url']);
      foreach($roles as $role){
          $active = ($arr[2] == $role->name)?'active':'';
          echo "<li class='$active' data='".$role->id."'>".
            '<a href="'.$role->name.'">'.$role->display_name.'</a>'.
          '</li>';
      }
      ?>
       <a href="/help/index" data-toggle="modal" class="btn btn-default btn-sm float-right">发布帖子</a>
       <a href="#add_user" data-toggle="modal" class="btn btn-default btn-sm float-right">创建账号</a>
       <a href='#' class='float-right btn'>总兼职人数: 52 , 总结算金额:3838</a>
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
                { data: "total_day", name:"总工时/d"},
                { data: "current_day", name:"当前未结算工时/d"},
                { data: "username", name: "用户名"},
                { data: "create_time", name:"创建时间"},
                { data: "nickname", name: "昵称"},
                { data: "avatar", name: "头像", orderable:false },
                { data: "sex", name:"性别"},
                { data: "asks_count", name:"求助数"},
                { data: "replies_count", name:"作品数"},
                { data: "fans_count", name:"粉丝数"},
                { data: "uped_count", name:"被赞数"},
                { data: "inform_count", name:"被举报数"},
                { data: "data", name: "操作", orderable:false}
            ],
            "ajax": {
                "url": "/waistcoat/list_users?role_id=" + role_id
            }
        },

        success: function(data){
            $(".remark").click(function(){
                var nickname = $(this).attr('nickname');
                $("#remark_user input[name='nickname']").val(nickname);

                var uid = $(this).attr('uid');
                $("#remark_uid").val(uid);

                var password = $(this).attr('password');
                $("#remark_user input[name='password']").val(password);

                var remark = $(this).attr('remark');
                $("#remark").val(remark);
            });
        },
    });
});
</script>

