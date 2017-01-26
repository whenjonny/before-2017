<script src="jquery.min.js" type="text/javascript"></script>
<script src="jquery.uploadify.min.js" type="text/javascript"></script>
<body>
	<h1>Uploadify Demo</h1>
	<form>
		<div id="queue"></div>
		<input id="file_upload" name="file_upload" type="file" multiple="true">
	</form>

	<script type="text/javascript">
		<?php $timestamp = time();?>
		$(function() {
            setTimeout(function(){
                $('#file_upload').uploadify({
                    'formData'     : {
                        'timestamp' : '<?php echo $timestamp;?>',
                        'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
                    },
                    'swf'      : 'uploadify.swf',
                    'uploader' : 'uploadify.php'
                });
            },10);
		});
	</script>
</body>
</html>
