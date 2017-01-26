<link rel="stylesheet" href="/mobile/css/common.css">
<link rel="stylesheet" href="/mobile/css/style.css">
<link rel="stylesheet" href="/mobile/css/icomoon/style.css">
<!-- 首页头部 -->
<div class="weixin">
<?php
foreach($data as $row){
?>
<section class="bg-color">
	<section class="wn-head">
        <span class="portrait">
            <img src="/mobile/img/bg.jpg" alt="头像">
        </span>
        <div class="wn-name name-cr"><?php echo $row['nickname'];?></div>
        <div class="wn-time time-cr"><?php echo $row['create_time'];?></div>
	</section>
	<section class="weixin-main">
        <img src="<?php echo $row['avatar'];?>" alt="求助图片">
		<div class="wnTab">
			<div class="dotTab"></div>
			<span class="dotBack"></span>
			<div class="wn-icon-tab icon-tab"></div>
			<div class="wn-font-tab">暗示法法师</div>
		</div>
	</section>
	<section class="columnIcon border-bm">
		<span class="ellipsis color-icon">
	        <i class="icon-ellipsis"></i>
		</span>
		<div class="threeIcon">
			<div class="assist color-icon">
				<i class="icon-assist"></i>
                <span class="count"><?php echo $row['up_count'];?></span>
			</div>
			<div class="bubble color-icon">
				<i class="icon-bubble"></i>
				<span class="count"><?php echo $row['share_count'];?></span>
			</div>
			<div class="comment color-icon">
				<i class="icon-comment"></i>
				<span class="count conment-margin"><?php echo $row['comment_count'];?></span>
			</div>
		</div>
	</section>
	<!-- P图数量 -->
	<section class="portrait-count">
		<div class="P-portrait">
        <div class="pCount"><?php echo $row['reply_count']; ?>人P过</div>
            <?php
            foreach($row['replyer'] as $replyer){
                echo '<img class="pHead" src="'.$replyer['avatar'].'"  alt="P图人头像">';
            }
            ?>
		</div>
	</section>
  </section>
<?php
}
?>
</div>
