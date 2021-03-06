<script type="text/javascript" src="/js/jquery-1.11.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/page.css">
<link rel="stylesheet" type="text/css" href="/css/common.css">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/icomoon/style.css">
<link rel="stylesheet" type="text/css" href="/theme/assets/global/plugins/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="/tapmodo/css/jquery.Jcrop.css">


<div class="modal hide fade in" id="viewPhotoModal" style="min-width: 760px; display: block; position: fixed;" aria-hidden="false">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3>上传图片</h3>
		<div class="text-right" id="select-value">
			<a href="#" class="upload-border crop" data="3/4">
				<i class="rectangle"></i>
				<span class="proportion" >3:4</span>
			</a>
			<a href="#" class="upload-border crop" data="4/3">
				<i class="rectangle1"></i>
				<span class="proportion">4:3</span>
			</a>
			<a href="#" class="upload-border crop" data="1">
				<i class="rectangle2"></i>
				<span class="proportion margin-right">1:1</span>
			</a>
			<a href="#" class="upload-border crop color-class" data="0">
				<span class="proportion master-margin">原图</span>
			</a>
		</div>
	</div>
	<div class="modal-body" id="viewPhotoModalBody">
		<div class="upload-photo">
			<div  class="photo-width display-photo" >
				<div id="labelboard" class="labelboard" >
					<img src=""  id="preview" class="img-display">
                    <img src=""  id="label" class="label-display" style="position: relative">
					<div class="fileQueue-display" id="fileQueue"></div>
				</div>
			</div>	   
		</div>
		<div class="upload-right">
			<div class="font-ones">第一步:上传图片</div>
			<div class="upload-button">
				<input type="file" id="uploadify" value="上传图片" >
			</div>
			<div class="font-ones">第二步:裁剪图片</div>
			<div class="upload-button" >
				<button class="btn btn-inverse confirm-color"  id="crop_image">确定截图</button>
			</div>
			<div class="font-ones">第三步:添加标签</div>
			<div class="upload-button">
				<div class="remind-load remind-padding">点击图片添加标签</div>
				<div class="remind-load remind-margin">告诉大神你要的效果</div>
				<button class="btn btn-inverse add-ask-btn"  id="save_ask">完成</button>
			</div>
		</div>
	</div>
</div>
<div class="modal-backdrop"></div>


<script type="text/javascript" src="/uploadify/jquery.min.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/uploadify/jquery.min.js"></script>
<script type="text/javascript" src="/uploadify/jquery.uploadify.min.js"></script>
<script type="text/javascript" src="/tapmodo/js/jquery.Jcrop.js"></script>
<script type="text/javascript" src="/jqdrag/jquery-drag.js"></script>
<script type="text/javascript" src="/theme/assets/scripts/common.js"></script>
<script type="text/javascript" src="/js/ask/add.js"></script>
