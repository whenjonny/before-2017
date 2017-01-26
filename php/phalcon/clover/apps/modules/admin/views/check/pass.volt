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

{% include "check/search_user.volt" %}


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
      <li>
        <a href="/check/delete">
          已删除</a>
      </li>
</div>

<?php modal("/check/preview"); ?>
<table class="table table-bordered table-hover" id="check_ajax"></table>

<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        src: $("#check_ajax"),
        dataTable: {
            "columns": [
                { data: "id", name: "ID" },
                { data: "score", name: "分数"},
                { data: "auditor", name: "审核人"},
                { data: "username", name: "姓名" },
                { data: "create_time", name:"创建时间"},
                { data: "thumb_url", name:"作品内容"},
                //{ data: "desc", name:"描述"}
                //{ data: "delete", name:"删除作品"}
            ],
            "ajax": {
                "url": "list_replies?status=1"
            },
            /*
            "order":[
              [3,"asc"]
              ]
             */
        },
        success: function(data){
            $(".pass").click(function(){

            });

            $(".del").click(function(){
                var target_id = $(this).attr("data");
                if(confirm("确认删除作品?")){
                    $.post("set_status", {
                        reply_id: target_id,
                        status: 0
                    }, function(){
                        toastr['success']("删除成功");
                        table.submitFilter();
                    });
                }
            });


        }
    });

    $('#check_ajax').on('click', '.preview_link', function(e){
        e.preventDefault();
        var src = $(this).children('img').attr('src');
        var prv_modal = $('#preview_modal');
        prv_modal.find('#preview_image').attr('src', src);
        prv_modal.find('#preview_image').css('width', '500px');
        prv_modal.find('#preview_image').css('height', 'auto');
        prv_modal.modal("show");

        return false;
    });
});
</script>
