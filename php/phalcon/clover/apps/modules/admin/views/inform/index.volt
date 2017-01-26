
<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>举报记录</li>
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
        <button type="submit" class="form-filter form-control" id="search" >搜索</button>
    </div>
</div>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="inform_type pending">
        <a href="/inform/index?type=pending">
        待处理</a>
      </li>
      <li class="inform_type resolved">
        <a href="/inform/index?type=resolved">
        已处理</a>
      </li>
      <li class="inform_type all">
        <a href="/inform/index?type=all">
        所有</a>
      </li>
    </ul>
</div>

<table class="table table-bordered table-hover" id="list_inform_ajax"></table>

<?php modal('/user/add_user'); ?>
<?php modal('/user/remark_user'); ?>

<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
<script>
var table = null;
jQuery(document).ready(function() {
    var filter = getQueryVariable('type');
    if( !filter ){
        filter = 'pending';
    }
    $('li.inform_type.'+filter).addClass('active');


    var columns = [
                { data: "content", name:"举报内容"},
                { data: "object", name:"被举报对象"},
                { data: "oper", name: "操作"}
            ];
    if( filter == 'all' ){
        columns[2] = { data: 'status', name: '状态' };
    }

    table = new Datatable();
    table.init({
        src: $("#list_inform_ajax"),
        dataTable: {
            "columns": columns,
            "ajax": {
                "url": "/inform/list_reports?type="+filter
            }
        },
        success: function( data ){
            // do nothing
        }
    });

    $('#list_inform_ajax').on('click','a.deal_inform', function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
        $.post('/inform/deal',{'type': type, 'id': id}, function( data ){
            if( data.ret == 1 ){
                alert(data.data);
                table.submitFilter();
            }
            else{
                alert( '处理失败' );
            }
            return true;
        });
        return true;
    });
});
</script>


