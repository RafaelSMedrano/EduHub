<?php

use humhub\modules\user\models\forms\Registration;
use humhub\widgets\SiteLogo;
use humhub\compat\HForm;
//use yii\bootstrap\ActiveForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\widgets\AuthChoice;
use humhub\libs\Html;

/**
 * @var $hForm Registration
 * @var $showAuthClients bool
 */

$this->pageTitle = Yii::t('UserModule.auth', 'Create Your EduHub profile');
?>

<div class="container" style="text-align: center;">
    <?= SiteLogo::widget(['place' => 'login']) ?>
    <br/>
    <div class="row">
        <div id="create-account-form" class="panel panel-default animated bounceIn"
             style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading">
                <?= Yii::t('UserModule.auth', '<strong>Account</strong> registration') ?>
            </div>
            <div class="panel-body">

                
                

                
                <div class="help-block">
                    <?= Yii::t('UserModule.account', 'Here you can edit your general profile data, which is visible in the about page of your profile.'); ?>
                </div>
                <?php $form = ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none'],  'acknowledge' => true]); ?>
                    <?= $hForm->render($form) ?>
                <?php ActiveForm::end(); ?>
                
                
                

            </div>
        </div>
    </div>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // set cursor to login field
        $('#User_username').focus();

        // set user time zone val
        $('#user-time_zone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);
    })

    // Shake panel after wrong validation
    <?php foreach ($hForm->models as $model) : ?>
        <?php if ($model->hasErrors()) : ?>
            $('#create-account-form').removeClass('bounceIn');
            $('#create-account-form').addClass('shake');
            $('#app-title').removeClass('fadeIn');
        <?php endif; ?>
    <?php endforeach; ?>

</script>
