<?php


use humhub\widgets\SiteLogo;
use humhub\compat\HForm;
use yii\bootstrap;
use humhub\modules\ui\form\widgets\ActiveForm;

use humhub\libs\Html;

/**
 * @var $hForm Registration
 * @var $showAuthClients bool
 */

$this->pageTitle = Yii::t('UserModule.account', 'Create Your EduHub profile');
?>

<div class="container" style="text-align: center">
    <?=  '<p style="font-family: Open Sans; color: #FFFFFF; font-size: 36px;">EduHub</p>' //SiteLogo::widget(['place' => 'login']) ?>
    <br/>
    <div class="row" style="border: none;">
        <div id="create-account-form" class="panel panel-default animated bounceIn"
             style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading">
                <?= Yii::t('UserModule.auth', 'Cadastro <strong>EduHub</strong> ') ?>
            </div>
            <div class="panel-body">               
                

                
                <div class="help-block">
                    <?= Yii::t('UserModule.account', 'You can do it. Selecione os próximos tópicos com atenção, pois eles vão ajudar o EduHub a gerar recomendações efetivas para você (as respostas selecionadas poderão ser editadas posteriormente no seu perfil)'); ?>
                </div>
                <?php $form = ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none'],  'acknowledge' => true]); ?>
                    <?php if($fieldId == 27): ?>                        
                        Você é?
                        <br>
                    <?php endif; ?> 
                    <?php if($fieldId == 28): ?>
                        Qual seu objetivo? (escolha quantos quiser)
                        <br>
                    <?php endif; ?> 
                    <?php if($fieldId == 29): ?>
                        Se for aluno, selecione o estágio da jornada empreendedora que você está.
                    Se for entidade, selecione os estágios da jornada empreendedora que você oferece serviços
                    (escolha quantos quiser)
                    <br>
                    <?php endif; ?> 
                    <?php if($fieldId == 30): ?>
                        Perfil que você se identifica?
                        <br>
                    <?php endif; ?>
                    <?php if($fieldId == 31): ?>
                        Tipos de negócios de interesse? (selecione quantos quiser)
                        <br>
                    <?php endif; ?> 
                    <?php if($fieldId == 32): ?>
                        Tópicos de empreendedorismo de Interesse:
                        <br>
                    <?php endif; ?> 
                    <?php if($fieldId == 33): ?>
                        Mercados de interesse: (selecione quantos quiser)
                        <br>
                    <?php endif; ?>     
                    <?php if($fieldId == 33): ?>
                        Tecnologia de interesse: (selecione quantos quiser)
                        <br>
                    <?php endif; ?>  
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
