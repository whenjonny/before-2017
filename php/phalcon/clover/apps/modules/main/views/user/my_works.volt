<div class="personal-bg">
    <div class="personal">
        <?php $__dealUserInfo = $this->view->userInfo; ?>
        {% include "user/simpleInfoBar.volt" %}
    </div>
    <div class="content-hot">
        <!-- section -->
        <?php $__dealWorksInfo = $this->view->replies;?>
        {% include "common/works.volt" %}
    </div>
</div>
