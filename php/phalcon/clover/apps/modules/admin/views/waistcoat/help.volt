<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建账号</li>
</ul>

{% include "waistcoat/search_user.volt" %}

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
</div>

<table class="table table-bordered table-hover" id="waistcoat_ajax"></table>

<?php modal('/user/add_user'); ?>
<?php modal('/user/remark_user'); ?>

<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
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
                { data: "phone", name: "手机"},
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
                "url": "/waistcoat/list_users?role_id="+role_id
            }
        },
        success: function(data){
            $(".remark").click(function(){
                $("#remark_user #remark_nickname").val("");
                $("#remark_user #remark_uid").val("");
                $("#remark").val("");
                $("#remark_reset").attr("checked", false);
                $("#uniform-remark_reset .checked").removeClass("checked");

                var nickname = $(this).attr('nickname');
                $("#remark_user #remark_nickname").val(nickname);
                var uid = $(this).attr('uid');
                $("#remark_user #remark_uid").val(uid);
                var remark = $(this).attr('remark') || "姓名：\n支付宝账号：\n银行名称和银行卡账号：\nQQ号码：\nQQ昵称：";
                $("#remark").val(remark);
            });
        },
    });
});

</script>


