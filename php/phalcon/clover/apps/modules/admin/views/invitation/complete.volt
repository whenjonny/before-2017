<ul class="breadcrumb">
  <li>
    <a href="#">用户模块</a>
  </li>
  <li>创建账号</li>
</ul>

<form action="" class="form-max-size bottom-distance form-horizontal form-fourPart">
<div class="clearfix">
 <!-- big-section -->
  <div class="col-sm-4">
  <!-- section -->
    <div class="form-group">
      <label class="left-label control-label">ID:</label>
      <div class="right-input">
         <input type="text" class="form-control input-sm">
      </div>
    </div>
    <!-- section -->
    <div class="form-group">
      <label class="left-label control-label">ID:</label>
      <div class="right-input">
         <input type="text" class="form-control input-sm">
      </div>
    </div>
  </div>
  <!-- big-section -->
  <div class="col-sm-4">
    <div class="form-group">
      <label class="left-label control-label">用户名:</label>
      <div class="right-input">
         <input type="text" class="form-control input-sm">
      </div>
    </div>
  </div>
   <!-- big-section -->
  <div class="col-sm-4">
    <div class="form-group">
      <label class="left-label control-label">昵称:</label>
      <div class="right-input">
         <input type="text" class="form-control input-sm">
      </div>
    </div>
  </div>
</div>
<button type="submit" class="btn btn-primary custom-search-btn">搜索</button>
</form>

<div class="tabbable-line">
    <ul class="nav nav-tabs">
      <li class="active">
        <a href="/invitation/total">
        全部 </a>
      </li>
      <li>
        <a href="/invitation/help">
        求助 </a>
         </li>
    </ul>
</div>
<table class="table table-bordered table-hover" id="waistcoat_ajax"></table>


<script>
var table = null;
jQuery(document).ready(function() {    
    table = new Datatable();
    table.init({
        src: $("#waistcoat_ajax"), 
        dataTable: { 
            "columns": [
                { data: "uid", name: "帖子ID" },
                { data: "reg_time", name:"创建时间"},
                { data: "sex", name:"发贴姓名"},
                { data: "sex", name:"性别"},
                { data: "sex", name:"朋友圈外"},
                { data: "sex", name:"分享"},
                { data: "sex", name:"朋友圈内"},
                { data: "userurl", name: "分享好友" },
                { data: "username", name: "浏览数"},
                { data: "nickname", name: "作品数"},
                { data: "oper", name: "点赞"},
                { data: "oper", name: "举报数"},
                { data: "oper", name: "评论数"},
                { data: "oper", name: "P按钮"},
                { data: "oper", name: "下载数"},
                { data: "oper", name: "上传数"}
            ],
            "ajax": {
                "url": "/invitation/complete"
            }
        },

        success: function(data){
            $(".edit").click(function(){
                toastr['success']("标题", "内容");
            });
        },
  });
});
</script>

