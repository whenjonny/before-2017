
<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>发布管理</li>
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

<table class="table table-bordered table-hover" id="deploy_ajax"></table>

<?php modal('/user/add_user'); ?>
<?php modal('/user/remark_user'); ?>

<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#deploy_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "帖子ID" },
                { data: "reg_time", name:"发布时间"},
                { data: "username", name:"发布名称和内容"},
                { data: "ask_thumb_url", name:"求助图"},
                { data: "reply_thumb_url", name:"作品图"},
                { data: "check", name: "操作"}
            ],
            "ajax": {
                "url": "/deploy/list_deploys"
            }
        },
        success: function(data){
            $(".edit").click(function(){
             var role_id = $(this).attr("data");
                var tr = $(this).parent().parent();

                var role_name = tr.find(".db_role_name").text();
                var role_display_name = tr.find(".db_role_display_name").text();

                $("#add_new_role input[name='role_id']").val(role_id);
                $("#add_new_role input[name='role_name']").val(role_name);
                $("#add_new_role input[name='role_display_name']").val(role_display_name);
                $("#add_new_role").modal("show");

            });
        },
    });


    $("#add_new_role .cancel").click(function(){
        $("#add_new_role form")[0].reset();
    });

    $("#add_new_role .save").click(function(){
        $.post("/role/set_role", $("#add_new_role form").serialize(), function(data){
        });

        $("#add_new_role form")[0].reset();
        $("#add_new_role").modal("hide");
        table.submitFilter();
    });
});

/*
$(function () {
    $('#add_new_user').on('show.bs.modal', function () {
        alert("show");
    });

    $('#add_new_user').on('hide.bs.modal', function () {
        alert("close");
    })
});
 */
</script>


