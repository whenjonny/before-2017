<!-- section -->
    <div class="content-piece">
        {% for work in __dealWorksInfo %}
        <div class="section-list">
            <div class="section-wrok">
                <div class="mc-head padding-hot">
                    <a href="/user/profile/{{work['uid']}}">
                    <span class="ranking-sex">
                        <img src="{{work['avatar']}}">
                        <span class="sex bc-{% if work['sex'] == 1 %}blue{% else %}pink{% endif %} icon-{% if work['sex'] == 1 %}boy{% else %}girl{% endif %}"></span>
                    </span>
                    <span class="mc-name">{{work['nickname']}}</span>
                    </a>
                    {% if is_owner %}
                        <?php if(isset($work['status'])){ ?>
                        {% if is_parttime %}
                            <span class="work-score" reply_id="{{work['id']}}">
                            {% if work['status'] == 3 %}
                                审核中
                            {% elseif work['user_scores'] %}
                                {% if work['status'] == 1 %}
                                    {{work['user_scores']['score']}}
                                {% elseif work['status'] == 2 %}
                                    未通过({{work['user_scores']['content']}})
                                    <a href="#" class="work-delete" data-id="{{work['id']}}">删除</a>
                                {% endif %}
                            {% endif %}
                            </span>
                        {% endif %}
                        <?php } ?>
                        <a href="#" data="{{work['id']}}" dtype="{{work['type']}}" dtid="{{work['target_id']|default(work['id'])|default(0)}}" class="upload-work curcor">上传作品</a>
                        {% if router.getActionName() =="inprogress" %}
                            <a href="#" class="ig-delete" data-id="{{work['download_id']}}">删除</a>
                        {% endif %}
                    {% endif %}
                    <span class="mc-time">{{work['create_time']|time_in_ago}}</span>
                </div>
                <div class="picture-hot">
                    <?php
                        $work['image_url'] = watermark2( $work['image_url'] );
                        echo get_image_labels($work,300,400, (bool)($work['reply_count']==0));
                    ?>
                </div>
            </div>
        </div>
        {% endfor %}
        {{ page }} <br />
    </div>
