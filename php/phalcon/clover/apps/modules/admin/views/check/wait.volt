
<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>审核作品</li>
</ul>

{% include "check/search_user.volt" %}


<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="active">
        <a href="/check/wait">
          待审核 </a>
      </li>
      <li>
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
<?php modal("/check/evaluation"); ?>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable();
    table.init({
        dom: "<t><'row'<'col-md-5 col-sm-12'li><'col-md-7 col-sm-12'p>>",
        src: $("#check_ajax"),
        dataTable: {
            "columns": [
                { data: "id", name: "ID" },
                { data: "oper", name: "操作"},
                { data: "stat", name: "统计(通过/拒绝)"},
                { data: "username", name: "姓名" },
                { data: "create_time", name:"发布时间"},
                { data: "ask_image", name:"求助"},
                { data: "thumb_url", name:"作品内容"}
                //{ data: "delete", name:"删除作品"}
            ],
            "ajax": {
                "url": "list_replies?status=3"
            }
        },
        success: function(data){
            $('select.flexselect').flexselect({
                allowMismatch:true,
                allowMismatchBlank:true,
                preSelection:false
            }).siblings('input.flexselect').attr('placeholder','拒绝理由');

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

            $(".deny").click(function(){
                var reply_id = $(this).attr("data");
                $("#modal_evaluation").attr("data", reply_id);
            });

            $(".quick-deny").click(function(){
                var obj = {};
                obj.reply_id = $(this).attr('data');
                obj.status   = 2;
                obj.data     = $(this).text();

                $.post("set_status", obj, function(data){
                    toastr['success']("操作成功");
                    table.submitFilter();
                });
            });
            $('.reject_btn').on('click', function(){
                var obj = {};
                var row = $(this).parents('tr');
                obj.reply_id = row.find('td.db_id').text();
                obj.status   = 2;
                obj.data     = $(this).parents('td').find('input.flexselect').val().replace(/(\d{1,}\.)/,'');

                $.post("set_status", obj, function(data){
                    toastr['success']("操作成功");
                    table.submitFilter();
                });

            });

            $(".score").click(function(){
                var obj = {};
                obj.reply_id = $(this).attr('data');
                obj.status  = 1;
                obj.data    = parseInt($(this).text());

                $.post("set_status", obj, function(data){
                    toastr['success']("操作成功");
                    table.submitFilter();
                });
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

    function timer(time)
    {
        var end_time = time + 30*60*1000;
        var ts = (new Date(end_time)) - (new Date());//计算剩余的毫秒数

        if(ts == 1000*60*5){
            toastr['error']("审核提醒", "您有一条审核未处理");
        }

        if(ts < 0)
            return '';
        //var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10);//计算剩余的天数
        //var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10);//计算剩余的小时数
        var mm = parseInt(ts / 1000 / 60 % 60, 10);//计算剩余的分钟数
        var ss = parseInt(ts / 1000 % 60, 10);//计算剩余的秒数
        //dd = checkTime(dd);
        //hh = checkTime(hh);
        mm = checkTime(mm);
        ss = checkTime(ss);

        return "倒计时：" + mm + ":" + ss;
    }

    function checkTime(i)  {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    setInterval(function(){
        var ctimes = $(".create_time");
        for(var i = 0; i < ctimes.length; i ++) {
            var time_str = "" + new Date().getFullYear() + "-" + $(ctimes[i]).text();
            var time = new Date(time_str).getTime();
            $(ctimes[i]).next().text(timer(time));
        }
    });
});
</script>
<style>
.db_stat{
    min-width: 110px;
}
.db_create_time{
    min-width: 80px;
}
td.db_oper div {
    text-align: left;
}
td.db_oper div.li_container{
    font-size: 10px;
}
</style>
