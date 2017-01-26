
<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>审核作品</li>
</ul>

{% include "check/search_user.volt" %}

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li>
        <a href="/check/wait">
          待审核 </a>
      </li>
      <li>
        <a href="/check/pass">
         审核通过 </a>
      </li>
      <li class="active">
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
                { data: "uid", name: "ID" },
                { data: "auditor", name: "审核人"},
                //{ data: "oper", name: "操作"},
                { data: "username", name: "姓名" },
                { data: "create_time", name:"发布时间"},
                { data: "thumb_url", name:"作品内容"},
                //{ data: "desc", name:"描述"},
                { data: "content", name: "拒绝原因"},
                { data: "recover", name:"恢复作品"}
            ],
            "ajax": {
                "url": "list_replies?status=2",
            },
            "order":[
              [0,"asc"]
            ]
        },
        success: function(data){
            $(".edit").click(function(){

            });

            $(".recover").click(function(){
                var target_id = $(this).attr("data");
                if(confirm("确认恢复作品到审核?")){
                    $.post("set_status", {
                        reply_id: target_id,
                        status: 3
                    }, function(){
                        toastr['success']("恢复正常");
                        table.submitFilter();
                    });
                }
            });


        },
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
