<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>时间安排</li>
</ul>


<div class="form-inline">
    <div class="form-group">
        <input name="type" type="hidden" class="form-control form-filter" value="1"/>
        <input name="uid" class="form-filter form-control" placeholder="账号ID">
        <input name="nickname" class="form-filter form-control" placeholder="昵称">
        <input name="username" class="form-filter form-control" placeholder="用户名">
    </div>
    <div class="form-group">
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>

    <div class="form-group paid_count">
        <div class="item"><div class="title">待结算</div><div class="content"><?php echo $unpaid; ?></div></div>
        <div class="item"><div class="title">已结算</div><div class="content"><?php echo $paid; ?></div></div>
    </div>
</div>
<style>
    div.paid_count div {
        display: inline-block;
    }
    div.paid_count .item{
        border: 1px solid #DDDDDD;
    }
    div.paid_count .item > div{
        padding: 8px 13px;
    }
    div.paid_count .item .title{
        background-color: #EEEEEE;
    }
</style>
<?php modal('/user/add_user'); ?>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li>
        <a href="#" data="0">待生效</a>
      </li>
      <li class="active">
        <a href="#" data="1">生效中</a>
      </li>
      <li>
        <a href="#" data="2">已结束</a>
      </li>
      {%  if not is_staff %}
      <li>
        <a href="#" data="3">已结算</a>
      </li>
      <li>
        <a href="#" data="4">已删除</a>
      </li>
      {% endif %}
      {%  if is_staff %}
      <a href="#add_user" data-toggle="modal" class="btn btn-default btn-sm float-right">创建账号</a>
      {% endif %}
      <a <?php echo $is_staff?'': "href='/config/index?name=taff_time_price_rate'"; ?> class='float-right btn'>当前平均时薪：<?php echo $rate;?></a>

    </ul>
</div>

<style>
.db_oper.sorting{
    width: 85px;
}

</style>
<table class="table table-bordered table-hover" id="list_scores_ajax"></table>

<script>
var table = null;
var tableSelector = "#list_scores_ajax";
jQuery(document).ready(function() {


    $(".tabbable-line li a").click(function(){
        $(".tabbable-line li").removeClass("active");
        var type = $(this).attr("data");
        $("input[name='type']").val(type);
        $(this).parent().addClass("active");
        var cols = table.getTable().DataTable().columns([6,8,9,10,11,12,13,14]);
        if( type > 0 ){
            cols.visible(true);
        }
        else{
            cols.visible(false);
        }
        table.submitFilter();
    });
    table = new Datatable();

    table.init({
        src: $(tableSelector),
        dataTable: {
            "columns": [
                { data: "id", name: "排班ID" },
                { data: "uid", name:"账号ID"},
                { data: "username", name:"用户名"},
                { data: "nickname", name:"昵称"},
                { data: "start_time", name:"开始时间"},
                { data: "end_time", name:"结束时间"},
                { data: "avatar", name:"头像"},
                { data: "oper", name:"操作"},
                { data: "create_user_count", name:"创建账号数量"},
                { data: "avg_score", name:"平均审分"},
                { data: "total_score", name:"审核总金额"},
                { data: "verify_count", name:"审核数量"},
                { data: "pass_count", name:"审核通过"},
                { data: "reject_count", name:"审核拒绝"},
                { data: "delete_count", name:"帖子删除"},
                { data: "forbit_count", name:"禁言数"},
                { data: "delete_comment_count", name:"评论删除"},
                { data: "post_ask", name:"发布求p"}
            ],
            "ajax": {
                "url": "/scheduling/list_schedulings",
                "method":'post'
            }
        },
        success: function(data){
            $(".end_time").click(function(){
                var id = $(this).attr('data');
                var uid = $(this).parents('tr').find('td.db_uid').text();
                var href = $('div.top-menu').find('li.dropdown-user li:first a').attr('href');
                var ownUid = getQueryVariable( 'operate_id', href );
                if(confirm("确认结束时间?")){

                    $.post('/scheduling/end_time', {id: id}, function(data){
                        if(data.ret == 1){
                            if( ownUid == uid ){
                                location.href="/login/logout";
                            }
                            table.submitFilter();
                        }
                    });
                }
            });

            $(".delete").click(function(){
                var id = $(this).attr('data');

                if(confirm("确认删除安排?")){
                    $.post('/scheduling/del', {id: id}, function(data){
                        if(data.ret == 1)
                            table.submitFilter();
                    });
                }
            });

            $(".recover").click(function(){
                var id = $(this).attr('data');

                if(confirm("确认恢复安排?")){
                    $.post('/scheduling/recover', {id: id}, function(data){
                        if(data.ret == 1)
                            table.submitFilter();
                    });
                }
            });

        }
    });

    var uid = getQueryVariable('uid');
    if( uid ){
        $("#add_user input[name='role_id']").val( 3/* 兼职账号的role_id */); //后台人员创建账号默认是兼职
    }
});
</script>
