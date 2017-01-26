<?php if(!empty($__dealComments) > 0){ ?>
<div class="S_line1 comment-border-head">
	<div class="comment-list-section">
        <div class="{{__cmntListTitleClass}}">{{__cmntListTitle}}</div>
        <?php
            foreach($__dealComments as $comment){
        ?>
        <div class="comment-row" data-praised="0" data-id="<?php echo $comment['comment_id']; ?>">
		<div class="comment-avatar">
			<div class="comment-portrait">
            <a href="/user/profile/<?php echo $comment['uid']; ?>">
                <img src="<?php echo $comment['avatar']; ?>" alt="">
            </a>
			</div>
		</div>
		<div class="comment-name-message">
        <div class="comment-name">
            <a href="/user/profile/<?php echo $comment['uid']; ?>" class="cmnt_username" data-uid="<?php echo $comment['uid']; ?>">
                <?php echo $comment['nickname']; ?>:
            </a>
            <?php
				echo $comment['content'];
				if( !empty($comment['at_comment'] ) ){
					$max = min( count($comment['at_comment']), 2);
					for( $i=0; $i< $max; $i++ ){
						$prev = $comment['at_comment'][$i];
						echo '//<a href="/user/profile/',$prev['uid'],'">@', $prev['nickname'],'</a>:&nbsp;', $prev['content'];
					}
				}
			?>
        </div>
			<div class="comment-time-section">
				<div class="comment-reply">
					<ul class="clearfix">
						<?php if( $this->view->_uid ): ?>
						<li class="comment-reply-button">
							<span class="comment-line S_line1">
								<a href="#" class="reply_comment">回复</a>
							</span>
						</li>
						<?php endif; ?>
						<li>
							<span class="line S_line1 cursor{% if comment['uped'] == 1 %} click-color{% endif %}" data-action="praise">
								<i class="icon-praise"></i>
								<em><?php echo $comment['up_count']; ?></em>	
							</span>
						</li>
					</ul>
				</div>
                <div class="comment-time"><?php echo time_in_ago($comment['create_time']); ?></div>
			</div>
        </div>
        </div>
        <?php
        }
        ?>
	</div>
</div>
<?php } ?>

