<ul class="breadcrumb"> <li>
    <a href="#">运营模块</a>
  </li>
  <li>发布审核</li>
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
<table class="table table-bordered table-hover" id="datatable_ajax"></table>


<script>
var table = null;
jQuery(document).ready(function() {
    table = new Datatable({});
    table.init({
        dom: "<t><'row'<'col-md-5 col-sm-12'li><'col-md-7 col-sm-12'p>>",
        src: $("#datatable_ajax"),
        dataTable: {
            "columns": [
                { data: "uid", name: "#" },
                { data: "userurl", name: "头像" },
                { data: "username", name: "用户名"},
                { data: "nickname", name: "昵称"},
                { data: "oper", name: "操作"}
            ],
            "ajax": {
                "url": "/example/list_users"
            }
        },

        success: function(data){
            $(".edit").click(function(){
                toastr['success']("标题", "内容");
            });

            $("#review_ajax_wrapper div:first").removeClass("table-scrollable");
        },
	});
});
</script>

</div>
