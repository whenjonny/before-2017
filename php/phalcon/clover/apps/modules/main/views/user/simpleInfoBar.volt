<!-- 头部 -->
    <div class="comment-head">
        <div class="comment-center head-portrait">
          <div class="pf_photo">
            <span class="ranking-sex" data-id="{{__dealUserInfo['uid']}}">
                <img src="{{__dealUserInfo['avatar']|default('/img/avatar.jpg')}}" alt="">
            </span>
          </div>
        </div>
        <div class="name-center">
            {{__dealUserInfo['nickname']}}
            <i class="sex-head bc-{% if __dealUserInfo['sex'] == 1 %}blue{% else %}pink{% endif %} icon-{% if __dealUserInfo['sex'] == 1 %}boy{% else %}girl{% endif %}"></i>
            <span class="profile-count-score">
            {% if is_parttime %}
                待结算:{{__dealUserInfo['current_score']}} 已结算:{{__dealUserInfo['paid_score']}}
            {% endif %}
            点赞数:{{__dealUserInfo['total_praise']}}</span>
        </div>
        

        {% if is_owner == 0  %}
        <!--
        <div class="pf_opt">
            <div class="opt_box">
                <div class="opt_box_1">
                    {% if is_fellow == 0 %}
                    <a href="#" class="W_btn_c btn_34px follow" data="{{uid}}">+ 关注</a>
                    {% else %}
                    <a href="#" class="W_btn_c btn_34px unfollow" data="{{uid}}">+ 取消关注</a>
                    {% endif %}
                </div>
                <div class="opt_box_1">
                    <a href="" class="W_btn_d btn_34px">私聊</a>
                </div>
            </div>
        </div>
        -->
        {% endif %}
     </div>
     <ul class="PCD_tab S_bg2">
        <li>
            <a class="tab_link<?php if($this->router->getActionName()=='profile'){ echo ' active';} ?>" href="/user/profile/{{__dealUserInfo['uid']}}">
                <i class="icon-camera"></i>求助
            </a>
        </li>
        <li>
            <a class="tab_link<?php if($this->router->getActionName()=='inprogress'){ echo ' active';} ?>" href="/user/inprogress/{{__dealUserInfo['uid']}}" >
                <i class="icon-underway"></i>进行中
            </a>
        </li>
        <li>
            <a class="tab_link<?php if($this->router->getActionName()=='my_works'){ echo ' active';} ?>" href="/user/my_works/{{__dealUserInfo['uid']}}" >
                <i class="icon-work"></i>作品
            </a>
        </li>
        <!--
        <li>
            <a class="tab_link<?php if($this->router->getActionName()=='my_collections'){ echo ' active';} ?>" href="/user/my_collections/{{__dealUserInfo['uid']}}" >
                <i class="icon-star-full"></i>收藏
            </a>
        </li>
        -->
     </ul>
    <?php modal('/user/edit', 'main'); ?>

<script>
$(function(){
    $(".section-list .upload-work").click(function(){
        var ask_id = $(this).attr('data');
        var dtype = $(this).attr('dtype');
        var dtid = $(this).attr('dtid');
        $("#add_reply_modal").attr("ask_id", ask_id);
        $("#add_reply_modal").attr("dtype", dtype);
        $("#add_reply_modal").attr("dtid", dtid);

        Common.toggle_modal('add_reply_modal');
        return false;
    });

    $(".work-delete").on('click', function(){
        var id = $(this).attr('data-id');
        $.post('/api/delete_work', {id: id}, function(data){
            if(data.ret == 1){
                alert("删除成功");
                location.reload();
            }
            else {
                alert("删除失败," + data.info);
            }
        });
        return false;
    });


    $(".ig-delete").on('click', function(){
        var id = $(this).attr('data-id');
        $.post('/api/delete_progress', {id: id}, function(data){
            if(data.ret == 1){
                alert("删除成功");
                location.reload();
            }
            else {
                alert("删除失败," + data.info);
            }
        });
        return false;
    });

});
</script>
