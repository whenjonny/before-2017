<!-- 评论列表 -->
<div class="comment-list">
	<div class="comment-padding">
		<!-- 评论对话框 -->
		<form class="comment-border-head comment-form">
			<div>
				<div class="comment-portrait">
					<img src="{{_avatar|default('/img/avatar.jpg')}}" alt="">
				</div>
				<div class="comment-publish">
	                <div class="p-input">
	                    <input name="type" type="hidden" value="<?php echo $__dealReply['type'];?>"/>
	                    <input name="target_id" type="hidden" value="<?php echo $__dealReply['id'];?>"/>
	                    <input name="reply_to" type="hidden" value="0"/>
	                    <input name="for_comment" type="hidden" value="0" />
						<textarea name="content" class="w_input" maxlength="100"></textarea>
					</div>
				</div>
			</div>
			<div class="comment-button-height">
				<?php if( $this->view->_uid): ?>
				<div class="comment-button">
					<a href="#" class="W_btn_a W_btn_a_disable comment_btn" >
						评论
					</a>
				</div>
				<?php else: ?>
					登陆后才能评论哦！
				<?php endif; ?>
			</div>
		</form>

	    <!-- 热门评论 -->
	    <?php
			//热门评论
			$__dealComments = array();
			if(isset($__dealReply['comments']['hot_comments'])){
				$__dealComments = $__dealReply['comments']['hot_comments'];
			}
			$__cmntListTitle = '热门评论';
			$__cmntListTitleClass = 'hot-comment-title hot-comment-color';
		?>
		{% include "comment/commentList.volt" %}

		<!-- 最新评论 -->
		<?php
			//最新评论
			$__dealComments = array();
			if( isset($__dealReply['comments']['new_comments']) ){
				$__dealComments = $__dealReply['comments']['new_comments'];
			}
			$__cmntListTitle = '最新评论';
			$__cmntListTitleClass = 'hot-comment-title newest-comment-color';
	    ?>
		{% include "comment/commentList.volt" %}

    </div>
    <?php if( $this->router->getControllerName() !="comment" && $__dealReply['comment_count'] > 10 ){ ?>
    <a href="/comment/show?type=2&target_id=<?php echo $__dealReply['id']; ?>&page=2" class="WB_cardmore clearfix" target="_blank">
		<span class="more_txt">
			后面还有{{__dealReply['comment_count'] - 10}}条评论,点击查看
		</span>
    </a>
    <?php } else { ?>
    	{{ page }}
    <?php } ?>
</div>