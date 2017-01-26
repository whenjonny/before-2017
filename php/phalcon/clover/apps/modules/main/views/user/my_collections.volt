<div class="personal-bg">
    <div class="personal">
        <div class="pl-bg">
            <div class="head-portrait">
                <span class="ranking-sex" data="{{uid}}">
                    <img src="{{user.avatar}}" alt="">
                    <span class="sex bc-{% if user.sex == 1 %}blue{% else %}pink{% endif %} icon-{% if user.sex == 1 %}boy{% else %}girl{% endif %}"></span>
                </span>
            </div>
            <div class="name-center">
                {{user.nickname}}
            </div>
            <div class="pl-count">
                <label for="">关注:<span>{{user.fellow_count}}</span></label>
                <label class="pl-border">粉丝:<span>{{user.fans_count}}</span></label>
                <label for="">赞:<span>{{user.uped_count}}</span></label>
            </div>
        </div>
            <div class="pl-tab">
                <a href="/user/profile/{{uid}}">
                    <span class="tab-section">
                        <img src="/img/camera.png" alt="">
                        <span>我的求P</span>
                    </span>
                </a>
                <a href="/user/inprogress/{{uid}}">
                    <span class="tab-section">
                        <img src="/img/underway.png" alt="">
                        <span>进行中</span>
                    </span>
                </a>
                <a href="/user/my_works/{{uid}}">
                    <span class="tab-section">
                        <img src="/img/work.png" alt="">
                        <span>我的作品</span>
                    </span>
                </a>
<!--                 <a href="/user/my_collections/{{uid}}">
                    <span class="tab-section tab-bg">
                        <img src="/img/collect.png" alt="">
                        <span>我的收藏</span>
                    </span>
                </a> -->
            </div>
            <div class="content-hot">
            <!-- section -->
            {% for collection in collections %}
            <div class="section-list">
                <div class="section-wrok">
                    <div class="mc-head padding-hot">
                        <a href="/user/profile/{{collection.uid}}">
                            <span class="ranking-sex">
                            <img src="{{collection.avatar}}" alt="">
                            <span class="sex bc-pink icon-boy"></span>
                        </span>
                        <span class="mc-name">{{collection.nickname}}</span>
                        </a>
                        <span class="mc-time">{{collection.create_time|time_ymd}}</span>
                    </div>
                    <div class="picture-hot">
                        <img src="{{collection.image_url|get_cloudcdn_url}}" alt="">
                    </div>
                </div>
            </div>
            {% endfor %}
            {{ page }} <br />
    </div>
</div>
