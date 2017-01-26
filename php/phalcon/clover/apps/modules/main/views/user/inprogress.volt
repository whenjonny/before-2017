<div class="content-inprogress">
    <div class="personal">
        <?php $__dealUserInfo = $this->view->userInfo; ?>
        {% include "user/simpleInfoBar.volt" %}
    </div>

    <div class="content-hot">
        <?php $__dealWorksInfo = $this->view->worksInfo;?>
        {% include "common/works.volt" %}
    </div>
</div>
<?php modal('/ask/view', 'main'); ?>

