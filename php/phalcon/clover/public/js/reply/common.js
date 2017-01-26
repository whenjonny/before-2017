$(function(){
	$('div.reply').on('click', 'div.share-friend[data-action="praise"]', function(){
        check_login();
        var replyEle = $(this).parents('div.reply')
        var reply_id = replyEle.attr('data-id');
        var status = replyEle.attr('data-praised')==="0"?1:0;
        var countEle = $(this).find('span.praise-amount');
        var clkEle = $(this);

        $.get('/reply/upReply/'+reply_id+'?status='+status,function(res){
            switch( res.ret ){
                case 0:
                    alert('失败');
                    break;
                case 1:
                    if(status == 0){
                        clkEle.removeClass('click-color');
                        decCommentPraise(countEle);
                    }
                    else if(status == 1){
                        clkEle.addClass('click-color');
                        incCommentPraise(countEle);
                    }
                    break;
                case 2:
                    clkEle.addClass('click-color');
                    status = 1;
                    alert('你已经点过赞');
                    break;
            }
            replyEle.attr('data-praised', status);
        });
    });

    $('div.reply').on('click', '.inform-btn', function(e){
        check_login();
        e.preventDefault();
        var replyEle = $(this).parents('div.reply')
        var reply_id = replyEle.attr('data-id');
        var status = parseInt(replyEle.attr('data-informed'));

        $.get('/reply/informReply/'+reply_id, function(res){
            switch( res.ret ){
                case 0:
                    alert('失败');
                    break;
                case 1:
                    alert('举报成功');
                    break;
                case 2:
                    alert('你已经举报过');
                    break;
            }
            replyEle.attr('data-informed', 1);
        });
    });
});

function incReplyPraise( countEle ){
    updateReplyPraise( countEle, 1 );
}

function decReplyPraise( countEle ){
    updateReplyPraise( countEle, -1 );
}

function updateReplyPraise( countEle, val ){
    countEle.text( Math.max(parseInt(countEle.text())+val,0) );
}
