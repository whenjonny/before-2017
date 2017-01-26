<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>用户列表</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="uid" class="form-filter form-control" placeholder="账号ID">
    </div>
    <div class="form-group">
        <input name="username" class="form-filter form-control" placeholder="角色名称">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="展示名称">
    </div>
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<table class="table table-bordered table-hover" id="list_users_ajax"></table>

<?php modal('/role/assign_role'); ?>

<script>
var table = null;
var master_text = [ '标记大神', '取消标记大神'];
$(function() {
    table = new Datatable();
    table.init({
        src: $("#list_users_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "账号ID" },
                { data: "create_time", name:"注册时间"},
                { data: "phone", name:"手机号"},
                { data: "nickname", name: "昵称"},
                { data: "sex", name:"性别"},
                { data: "avatar", name:"头像"},
                { data: "asks_count", name:"求P数"},
                { data: "replies_count", name:"作品数"},
                { data: "download_count", name:"下载数"},
                { data: "inprogress_count", name:"进行中"},
                { data: "upload_count", name:"上传"},
                { data: "uped_count", name: "点赞总数" },
                { data: "total_inform_count", name: "举报数"},
                { data: "share_count", name: "分享"},
                { data: "wxshare_count", name: "分享朋友圈"},
                // { data: "friend_share_count", name: "分享好友"},
                { data: "comment_count", name: "评论数"},
                { data: "focus_count", name: "关注数"},
                { data: "fans_count", name: "粉丝数"},
                { data: "fellow_count", name: "互粉数"},
                { data: "username", name:"用户名"},
                { data: "forbid", name: "禁言"},
                { data: "assign", name: "角色"},
                { data: "master", name: "大神"}
            ],
            "ajax": {
                "url": "/personal/list_users"
            }
        },

        success: function(data){
            $(".edit").click(function(){
                toastr['success']("标题", "内容");
            });

            $(".forbid").click(function(){
                var obj = {};
                obj.uid     = $(this).attr("uid");
                obj.value   = $(this).attr("data");
                $.post("/user/forbid_speech", obj, function(data){
                    if(data.ret == 1){
                        toastr['success']("", "操作成功");
                        table.submitFilter();
                    }
                });
            });
        },
    });


    $('#list_users_ajax').on('click','.assign',function(){
        var current_modal = $('#assign_role_modal');
        var current_row = $(this).parents('tr');
        var user_id = current_row.find('.db_uid').text();
        var username = current_row.find('.db_username').text();

        current_modal.find('input[name="user_id"]').val(user_id);
        current_modal.find('input[name="user_name"]').val(username);

        $.post('/role/list_roles',function(result){
            result = JSON.parse(result);
            var per_ul = $('.role_list');
            per_ul.empty();

            var role_list = result.data;
            if( !role_list.length ){
                alert('获取角色列表失败！');
                return;
            }

            var role_list_length = role_list.length;
            for( var i=0; i<role_list_length; i++){
                var crnt_item = role_list[i];

                var per_item = $('<li>').addClass( 'role_item' );
                var pre_label = $('<label>').text( crnt_item['display_name'] );
                var per_checkbox = $('<input>').attr({
                        'type' : 'checkbox',
                        'name' : 'role_id[]',
                        'value': crnt_item['id']
                    })
                    .prependTo( pre_label );
                per_item.append( pre_label );
                per_ul.append( per_item );
            }
        });

        $.post('/role/get_roles_by_user_id',{'user_id':user_id},function(result){
            var role_ids = result.data.split(',');
            var per_box = $('input[name="role_id[]"]');

            for(var role_id in role_ids){
                per_box.filter('[value="'+role_ids[role_id]+'"]').attr({'checked':'checked'});
            }
        });

        $('#assign_role_modal').modal('show');
    });

    $('#list_users_ajax').on('click','.master', function(){
        var current_row = $(this).parents('tr');
        var user_id = current_row.find('.db_uid').text();

        if(confirm("确认设置/取消大神?")) {
            $.post('/personal/set_master',{'uid':user_id}, function(res){
                if( res.ret == 1 ){
                    toastr['success']('设置成功');
                    table.submitFilter();
                }
            });
        }
    })
});
</script>

