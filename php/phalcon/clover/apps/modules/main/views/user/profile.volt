<div class="personal-bg">
    <div class="personal">
        <?php $__dealUserInfo = $this->view->userInfo; ?>
        {% include "user/simpleInfoBar.volt" %}
        <div class="content-hot">
            <!-- section -->
            <?php $__dealWorksInfo = $this->view->asks; ?>
            {% include "common/works.volt" %}
        </div>
    </div>
</div>


<?php modal('/ask/view', 'main'); ?>
