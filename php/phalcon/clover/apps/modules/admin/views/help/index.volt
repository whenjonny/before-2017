<ul class="breadcrumb">
  <li>
    <a href="#">运营模块</a>
  </li>
  <li>发布求助</li>
</ul>

<table>
    <tr id="help_sample" class="hidden">
        <td>
            <div class="form-group">
                <select name="username" class="form-control " data-placeholder="选择账号">
                    <option value=""></option>
                    <?php
                    foreach($users as $user){
                        echo '<option value="'.$user->uid.'">'.$user->username.'</option>';
                    }
                    ?>
                </select>
            </div>
        </td>
        <td class="upload" >
            <div class="form-group">
                <div class="select-picture">
                    <input type="text" class="form-control" maxlength="25" name="upload[]" >
                    <input type="file" id="upload_id"/>
                </div>
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="text" class="form-control" maxlength="25" name="label[]" >
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="number" class="input-xsmall form-control inline" maxlength="2" name="hour[]" > 小时
                <input type="number" class="input-xsmall form-control inline" maxlength="2" name="min[]" > 分钟后
            </div>
        </td>
    </tr>
</table>

<form id="help_form">
<table id="help_table" class="table table-bordered table-hover">
    <thead>
        <tr>
        <th>发布人名称</th>
        <th>选择求P或作品图片</th>
        <th>求助或作品内容</th>
        <th>选择发布时间</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><a href='#' id="add_row">添加一行</a></td>
        </tr>
    </tfoot>
</table>
</form>
<button type="button" class="form-filter form-control" id="commit">提交</button>


<script type="text/javascript" src="/theme/assets/global/plugins/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="/theme/assets/global/plugins/select2/select2.css"/>
<link href="/theme/assets/global/css/plugins.css" id="style_components" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
 
<script>

function add_row(){
    var count = $("#help_table tbody tr").length;
    var row = "<tr>" + $("#help_sample").html() + "</tr>";
    $("#help_table tbody").append(row); 
    var tr  = $("#help_table tbody tr").last();

    var upload_id = "upload_" + count;
    tr.find("#upload_id").attr("id", upload_id);
 
    var select = tr.find("select[name='username']");
    var length = select.find("option").length;
    var index  = parseInt(Math.random()*length);
    var value  = select.find("option:eq("+index+")").attr("value");
    select.val(value==""?1:value);

    tr.find("select[name='username']").select2();

    Common.upload("#" + upload_id, function(data, upload_id){
        var name = data.data.name;
        var arr_1 = name.split(".");
        if(arr_1.length < 2) {
            alert("文件名格式不对");
            return false;
        }
        name = arr_1[arr_1.length - 2];
        var arr_2 = name.split("_");
        if(arr_2.length >= 2){
            name = arr_2[1];
        }

        var input = $(upload_id).parent().find("input[name='upload[]']");
        input.val(JSON.stringify(data.data));
        var label = $(upload_id).parents('.upload').next().find("input[name='label[]']");
        label.val(name);
    }, null, { url: '/image/upload'});

    return false;
}                        
                        
$(function(){
    $(".uploadify-button-text button").live('click', function(){return false; });

    $("#add_row").click(add_row);
    $("#commit").click(function(){
        var data = $('#help_form').serializeArray();
        if(data.length==0){
            toastr['error']('请先添加！');
            return;
        }
        $.ajax({
            url: '/help/set_asks',
            type: 'POST',
            dataType: 'json',
            cache: false,//POST请求不缓存
            data: data,
            timeout: 5000,
            success: function(result){
                toastr['error'](result.info);
                if(result.ret == 1)
                    location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest.status);
                console.log('err');
            }
        });
        console.log(data);
    });
});
</script>
