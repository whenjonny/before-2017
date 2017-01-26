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
      <a href="#add_user" data-toggle="modal" class="btn btn-default btn-sm float-right">创建账号</a>
      <a class='float-right btn'>总兼职人数: <?php echo $num;?>, 总结算金额: <?php echo $score; ?></a>
      <a href='/config/index?name=taff_time_price_rate' class='float-right btn'>时薪：<?php echo $rate;?>(点击修改)</a>
    </ul>
</div>
<style>
.db_rate.sorting {
    display: inline-block;
    width: 120px;
}
.db_rate .form-control {
    text-align: center;
}
.db_rate input {
    text-align: center;
    width: 50px;
    display: inline-block;
}
.db_rate button {
    text-align: center;
    width: 55px;
    display: inline-block;
    margin-left: -1px;
}
</style>
<table class="table table-bordered table-hover" id="waistcoat_ajax"></table>

<?php modal('/user/add_user'); ?>
<?php modal('/user/add_user_schedule'); ?>
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
                { data: "username", name:"用户名"},
                { data: "nickname", name:"昵称"},
                { data: "rate", name:"时薪"},
                { data: "total_hour", name:"总工时/h"},
                { data: "hour_money", name:"待结算"},
                { data: "paid_money", name:"已结算"},
                { data: "set_time", name:"设置时间"},
                { data: "money", name: "结算"},
                { data: "data", name: "操作", orderable:false},
                { data: "avatar", name: "头像", orderable:false },
                { data: "total_score", name:"总审分"},
                { data: "avg_score", name:"平均审分"},

                { data: "create_user_count", name:"创建账号数量"},
                { data: "verify_count", name:"审核数量"},
                { data: "pass_count", name:"审核通过"},
                { data: "reject_count", name:"审核失败"},
                { data: "delete_count", name:"帖子删除"},
                { data: "forbit_count", name:"禁言数"},
                { data: "delete_comment_count", name:"评论删除"},
                { data: "post_ask", name:"发布求p"}
            ],
            "ajax": {
                "url": "/waistcoat/list_users?role_id=" + role_id
            }
        },

        success: function(data){
            $(".rate_save").click(function(){
                var uid = $(this).attr("data");
                var val = $(this).prev().val();
                $.post("/config/set_person_rate", {
                    value: val,
                    uid: uid
                }, function(data){
                    if(data.ret == 1) {
                        toastr['success']("操作成功");
                        table.submitFilter();
                    }
                });
            });

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
                    $.post("/user/staff_paid", {uid: uid}, function(data){
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

            $(".set_time").click(function(){
                var name = $(this).attr("nickname");
                var uid  = $(this).attr("uid");

                var dialogBox = $('#add_user_schedule');
                dialogBox.find('input[name="user_name"]').val(name);
                dialogBox.find('input[name="user_id"]').val(uid);

                var dtpickerOption = {
                    format: 'yyyy-mm-dd hh:ii',
                    autoclose: true
                }
                $('input[name="start_time"], input[name="end_time"]').datetimepicker(dtpickerOption);
            });

            $("#add_user_schedule .save").click(function(){
                var inputs = $("#add_user_schedule input");
                for(var i = 0; i < inputs.length; i++){
                    var value = $(inputs[i]).val();
                    if(value == undefined || value == "" ){
                        toastr['error']("请输入" + $(inputs[i]).attr("name"));
                        return false;
                    }
                }

                var $dom = $('#add_user_schedule');
                var data = {
                    'uid'  : $dom.find('input[name="user_id"]').val(),
                    'start_time' : Date.parse( $dom.find('input[name="start_time"]').val() )/1000,
                    'end_time'   : Date.parse( $dom.find('input[name="end_time"]'  ).val() )/1000
                }

                $.post('/waistcoat/set_time', data, function(result){
                    if(result.ret==1){
                        $dom.modal("hide");
                        toastr['success']('添加成功');
                        table.submitFilter();  //刷新表格
                    }
                });
            });
        }
    });
});

</script>

