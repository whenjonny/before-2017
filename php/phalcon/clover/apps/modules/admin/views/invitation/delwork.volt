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
        <input name="username" class="form-filter form-control" placeholder="账号">
    </div>
    <div class="form-group">
        <input name="nickname" class="form-filter form-control" placeholder="昵称">
    </div>
    <div class="form-group">
        <input name="del_by" class="form-filter form-control" placeholder="删除账号">
    </div>
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li>
        <a href="/invitation/work">
        作品 </a>
      </li>
      <li>
        <a href="/invitation/help">
        求助 </a>
      </li>
      <li class="active">
        <a href="/invitation/delwork">
        已删除作品</a>
      </li>
      <li>
        <a href="/invitation/delhelp">
        已删除求助</a>
      </li>
    </ul>
</div>

<?php modal("/check/preview"); ?>
<table class="table table-bordered table-hover" id="waistcoat_ajax"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#waistcoat_ajax"),
        dataTable: {
            "columns": [
                { data: "id", name: "帖子ID" },
                { data: "deleteor", name: "删贴账号" },
                { data: "create_time", name:"作品创建时间"},
                { data: "recover", name: "恢复"},
                { data: "avatar", name:"发贴头像", orderable: false},
                { data: "username", name:"用户名"},
                { data: "nickname", name:"昵称"},
                { data: "thumb_url", name:"原图"},
                { data: "sex", name:"性别"},
                { data: "share_count", name:"分享数"},
                { data: "weixin_share_count", name:"朋友圈内"},
                // { data: "total_share", name: "分享好友" },
                { data: "click_count", name: "浏览数"},
                { data: "reply_count", name: "作品数"},
                { data: "up_count", name: "点赞"},
                { data: "inform_count", name: "举报数"},
                { data: "comment_count", name: "评论数"},
                { data: "download_times", name: "下载数"},
               /*
                { data: "oper", name: "P按钮"},
                { data: "uid", name:"创建时间"},*/
                { data: "status", name:"求P状态"}
            ],
            "ajax": {
                "url": "/help/list_works?status=0"
            }
        },

        success: function(data){
            $(".delete").click(function(){
            });

            $(".recover").click(function(){
                var target_id   = $(this).attr("data");
                var type        = $(this).attr("type");
                if(confirm("确认恢复作品?")){
                    $.post("/help/set_status", {
                        id: target_id,
                        type: type,
                        status: 1
                    }, function(){
                        toastr['success']("恢复成功");
                        table.submitFilter();
                    });
                }
            });
            $('#waistcoat_ajax').on('click', '.preview_link', function(e){
                e.preventDefault();
                var src = $(this).children('img').attr('src');
                var prv_modal = $('#preview_modal');
                prv_modal.find('#preview_image').attr('src', src);
                prv_modal.find('#preview_image').css('width', '500px');
                prv_modal.find('#preview_image').css('height', 'auto');
                prv_modal.modal("show");

                return false;
            });
        }
  });
});
</script>
