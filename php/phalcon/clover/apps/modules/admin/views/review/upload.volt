<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>批量发布求助</li>
</ul>
<?php
$arr = array();

$err_count = 0;
foreach($uploads as $upload){
    $filename   = explode("_", $upload->filename);
    $version    = explode(".", $filename[0]);
    if(sizeof($filename) < 2 || sizeof($version) < 2){
        $err_count ++;
        continue;
    }
    $name       = $filename[1];

    $upload->filename = $name;
    $arr[$version[0]][] = $upload->toArray();
}
?>

<table>
    <tr id="help_sample" class="hidden">
        <td>
            <div class="form-group">
                <select name="username" class="form-control " data-placeholder="选择账号">
                    <option value=""></option>
                    <?php
                    foreach($users as $user){
                        $name = ($user->nickname=="")?$user->phone: $user->nickname;
                        echo '<option type="'.$user->role_id.'" value="'.$user->uid.'">'.$name.'</option>';
                    }
                    ?>
                </select>
            </div>
        </td>
        <td class="upload" >
            <div class="form-group">
                <div class="select-picture">
                    <input type="text" class="form-control" maxlength="25" name="upload" >
                    <input type="file" id="upload_id"/>
                </div>
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="text" class="form-control" maxlength="25" name="label" >
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="number" class="input-xsmall form-control inline" maxlength="2" name="hour" value="0"> 小时
                <input type="number" class="input-xsmall form-control inline" maxlength="2" name="min" value="0" > 分钟后
            </div>
        </td>
        <td><a href='#' class='delete'>删除</a></td>
    </tr>
</table>

<form id="help_form">
<table id="help_table" class="table table-striped table-bordered table-advance table-hover">
    <thead>
        <tr>
        <th>发布人名称</th>
        <th>选择求P或作品图片</th>
        <th>求助或作品内容</th>
        <th>选择发布时间</th>
        <th>操作</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
<!--
    <tfoot>
        <tr>
            <td colspan="4"><a href='#' id="add_row">添加一行</a></td>
        </tr>
    </tfoot>
-->
</table>
</form>
<button type="button" class="form-filter form-control" id="commit">提交</button>
<script>
var work_ids = <?php echo json_encode($works); ?>;
var help_ids = <?php echo json_encode($helps); ?>;
var rows     = JSON.parse('<?php echo json_encode($arr); ?>');
</script>


<script type="text/javascript" src="/theme/assets/global/plugins/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="/theme/assets/global/plugins/select2/select2.css"/>
<link href="/theme/assets/global/css/plugins.css" id="style_components" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
 
<script>

function add_row(data, is_ask, is_append){
    var row_length = $(".edit_row").length;

    var count = $("#help_table tbody tr").length;
    var row = "<tr class='ask_row' data='"+row_length+"'>" +
                $("#help_sample").html() + "</tr>";
    if(is_append == undefined) {
        $("#help_table tbody").append(row); 
        var tr  = $("#help_table tbody tr").last();
    }
    else {
        $(is_append).before(row);
        var tr = $(is_append).prev();
        if(row_length != 0){
            tr.attr("data", tr.prev().attr("data"));
        }
    }

    var upload_id = "upload_" + count;
    tr.find("#upload_id").attr("id", upload_id);

    var select  = tr.find("select[name='username']");
    var value   = 0;
    if(is_ask){
        value = help_ids[parseInt(Math.random()*help_ids.length)];
    }
    else {
        value = work_ids[parseInt(Math.random()*work_ids.length)];
    }
    select.val(value);
    tr.find("select[name='username']").select2();

    Common.upload("#" + upload_id, function(data, upload_id){
        var input = $(upload_id).parent().find("input[name='upload']");
        input.val(JSON.stringify(data.data));
        var label = $(upload_id).parents('.upload').next().find("input[name='label']");
        label.val(data.data.name);
    }, null, { url: '/image/upload'});

    if(data){
        var upload_obj = {};
        upload_obj.url = data.qiniu_url;
        upload_obj.id  = data.id;
        upload_obj.name = data.filename;
        JSON.stringify(upload_obj);

        tr.find("input[name='label']").val(data.filename);
        tr.find("input[name='upload']").val(JSON.stringify(upload_obj));
    }

    return false;
}                        
                        
$(function(){
    $(".uploadify-button-text button").click(function(){return false; });

    for(var i in rows) {
        var is_ask = true;
        for(var j in rows[i]){
            add_row(rows[i][j], is_ask);
            if(is_ask){
                is_ask = false;
            }
        }
        
        $("#help_table tbody").append("<tr class='edit_row' style='background:#E08283;color:white;'><td colspan='5'><a style='color:white;' href='#'>添加一行</a></td></tr>"); 
    }

    $(".edit_row").click(function(){
        add_row(null, null, this);
        return false;
    });

    $(".delete").live('click', function(){
        $(this).parent().parent().remove();
    });

    $("#commit").click(function(){
        if(!confirm("确认提交批量发布?")){
            return false;
        }
        var data = [];
        var ask_rows = $(".ask_row");
        for(var i = 0; i < ask_rows.length; i ++){
            var row = $(ask_rows[i]);
            var obj = {};
            obj.key      = row.attr("data");
            obj.username = row.find("select[name='username']").val();
            obj.upload   = row.find("input[name='upload']").val();
            obj.label    = row.find("input[name='label']").val();
            obj.hour     = row.find("input[name='hour']").val();
            obj.min      = row.find("input[name='min']").val();

            for(var j in obj){
                if(j == 'label' || j == 'hour' || j == 'min'){
                    continue ;
                }
                if(obj[j] == undefined || obj[j] == ""){
                    toastr['error']('请输入' + j);
                    return false;
                }
            }

            data.push(obj);
        }
        
        if(data.length==0){
            toastr['error']('请先添加！');
            return;
        }

        $.post("set_batch_asks", {data: data}, function(data){
            if(data.ret == 1) {
                toastr['success']("保存成功");
                setTimeout(function(){
                    location.href='batch';
                }, 1000);
            }
        });
    });
});
</script>
