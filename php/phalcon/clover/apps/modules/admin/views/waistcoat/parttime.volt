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
        <a href='#' class='float-right btn'>总兼职人数: <?php echo $num;?>, 总结算金额: <?php echo $score; ?></a>
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
                { data: "phone", name:"手机"},
                { data: "username", name:"用户名"},
                { data: "create_time", name:"创建时间"},
                { data: "current_score", name: "待支付金额"},
                { data: "paid_money", name: "已支付金额"},
                { data: "money", name: "结算"},
                { data: "data", name: "操作", orderable:false},
                { data: "nickname", name:"昵称"},
                { data: "avatar", name: "头像", orderable:false },
                { data: "sex", name:"性别"},
                { data: "avg_points", name:"平均得分"},
                { data: "ask_count", name:"求助数"},
                { data: "total_replies_count", name:"总作品数"},
                { data: "passed_replies_count", name:"通过作品数"},
                { data: "rejected_replies_count", name:"拒绝作品数"},
                { data: "fans_count", name:"粉丝数"},
                { data: "uped_count", name:"被赞数"},
                { data: "inform_count", name:"被举报数"}
            ],
            "ajax": {
                "url": "/waistcoat/list_users?role_id=" + role_id
            }
        },

        success: function(data){
            $(".remark").click(function(){
                $("#remark_nickname").val("");
                $("#remark_password").val("");
                $("#remark").val("");
                var nickname = $(this).attr('nickname');
                var uid = $(this).attr('uid');
                var remark = $(this).attr('remark');

                $("#remark_nickname").val(nickname);
                $("#remark_uid").val(uid);
                $("#remark").val(remark);
            });

            $(".paid").click(function(){
                if(confirm("确认结算?")){
                    var uid = $(this).attr("uid");
                    $.post("/user/parttime_paid", {uid: uid}, function(data){
                        if(data.ret == 1) {
                            toastr['success']("操作成功");
                            table.submitFilter();
                        }
                    });
                }
            });

            $(".paid_list").click(function(){
                var data = $(this).attr("uid");
                location.href = "/score/index?operate_to="+data;
            });
        }
    });
});

</script>

