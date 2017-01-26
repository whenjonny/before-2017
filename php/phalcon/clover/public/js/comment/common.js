$(function(){
    $('div.comment-amount-btn').on('click', function(){
        var cmntListEle =  $(this).parents('.reply').find('.comment-list');
        cmntListEle.slideToggle(function(){
            if( cmntListEle.css('display') == 'block' ){
                cmntListEle.find('textarea[name="content"]').trigger('focus');
            }
        });
    });
    $('div.reply').on('focus', 'textarea[name="content"]', function(){
        $('textarea[name="content"]').removeClass('click-border-color');
        $(this).addClass('click-border-color');
    })
    .on('blur', 'textarea[name="content"]', function(){
        $(this).removeClass('click-border-color');
    })
    .on('keydown', 'textarea[name="content"]', function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13 && event.ctrlKey){
            var textareaBox = $('textarea.click-border-color');
            textareaBox.parents('form').submit();
        }
    });

    $('div.reply').on('submit', 'form.comment-form', function(e){
        check_login();
        e.preventDefault();
        var form = $(this);
        var target_id = $(this).parents('div.reply').attr('data-id');
        var btn = form.find('a.comment_btn.btn-disabled');
        if(form.find("textarea[name='content']").val() == ''){
            alert('评论内容不能为空');
            btn.removeClass('btn-disabled');
            return false;
        }

        $.post("/comment/save", form.serialize(), function(data){
            if(data.ret == 1){
                location.href='/comment/show/?type=2&target_id='+target_id;
            }
            btn.removeClass('btn-disabled');
        });
        return false;
    });

    $('a.reply_comment').on('click', function(event){
        event.preventDefault();
        var cmntRowEle = $(this).parents('div.comment-row');

        var cmntNameMsg = $(this).parents('div.comment-name-message');
        var cmntFormEle = cmntNameMsg.find('form.comment-form');
        if( cmntFormEle.length ){
            cmntFormEle.slideUp("normal", function(){
                cmntFormEle.remove();
            });
            return;
        }

        cmntFormEle = $(this).parents('div.comment-padding').children('form.comment-form').clone();
        var cmntBox = cmntFormEle.find('textarea[name="content"]');

        var replyWhoEle = cmntRowEle.find('a.cmnt_username');
        var replyName = replyWhoEle.text().trim();
        var replyId = cmntRowEle.attr('data-id');
        var replyUid = replyWhoEle.attr('data-uid');

        cmntBox.attr({'placeholder': '回复 '+replyName});
        cmntFormEle.find('input[name="reply_to"]').val(replyUid);
        cmntFormEle.find('input[name="for_comment"]').val(replyId);
        cmntFormEle.appendTo(cmntNameMsg).hide();
        cmntBox.trigger('focus');
        cmntFormEle.slideDown();
    });

    $('.comment-row').on('click', 'span[data-action="praise"]',function(){
        check_login();
        var commentEle = $(this).parents('div.comment-row');
        var comment_id = commentEle.attr('data-id');
        var status=commentEle.attr('data-praised')==="0"?1:0;
        var action=$(this).attr('data-action');
        var countEle = $('div.comment-row[data-id="'+comment_id+'"]').find('span[data-action="praise"]').find('em');
        var clkEle = $(this);

        $.get('/comment/upComment/'+comment_id,{ 'status': status },function(res){
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
            commentEle.attr('data-praised', status);
        });
    });

    $("div.reply").on('click','a.comment_btn:not(.btn-disabled)',function(e){
        check_login();
        e.preventDefault();
        var btn = $(this).addClass('btn-disabled');
        var form = $(this).parents('form.comment-form');
        form.submit();
        return false;
    });

});

function incCommentPraise( countEle ){
    updateCommentPraise( countEle, 1 );
}

function decCommentPraise( countEle ){
    updateCommentPraise( countEle, -1 );
}

function updateCommentPraise( countEle, val ){
    countEle.each(function(e){
        $(this).text( Math.max(parseInt($(this).text())+val, 0) );
    })
}
