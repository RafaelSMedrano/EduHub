<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\libs\Html;
use humhub\modules\admin\widgets\IncompleteSetupWarning;
use humhub\modules\analytics\models\Configuration;
use humhub\modules\analytics\Module;
use humhub\modules\analytics\permissions\ViewAnalytics;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;


/**
 * @var $this View
 * @var $model Configuration
 */

/** @var Module $module */
$module = Yii::$app->getModule('analytics');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::$app->user->can(ViewAnalytics::class) ?
            Button::defaultType(Yii::t('AnalyticsModule.config', 'View statistics'))
                ->link(['/analytics/admin/index'])
                ->icon($module->icon)
                ->right() : '' ?>

        <strong><?= $module->getName() ?></strong>

        <div class="help-block">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">
        <?= IncompleteSetupWarning::widget() ?>

        <div>
            <?= Yii::t('AnalyticsModule.config', 'This module records anonymous data on user visits to the platform and spaces, as well as the total number of members.') ?>
            <br>
            <?= Yii::t('AnalyticsModule.config', 'It also records user data such as browser, language, country of connection and date of last visit, but it does not record the IP address.') ?>
        </div>

        <br>

        <div class="alert alert-info">
            <?= Yii::t('AnalyticsModule.config', 'You can allow group members or space roles to view analytics in the permission settings.') ?>
        </div>

        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'chartType')->dropDownList($model->getChartTypeLabels()) ?>
        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>

        <br>
        <hr>
        <br>

        <div class="alert alert-warning">
            <p><?= Yii::t('AnalyticsModule.config', 'You can change some settings in your file configuration {filePath} ({moreInfo}):', [
                    'filePath' => Html::tag('code', '@humhub/protected/config/common.php'),
                    'moreInfo' => Html::a(
                        Yii::t('AnalyticsModule.config', 'more information here'),
                        'https://docs.humhub.org/docs/admin/advanced-configuration/#file-configuration',
                        ['target' => '_blank']
                    ),
                ]) ?></p>
            <p>
                <br>
                <strong>nbDaysUserDataRetention</strong> - Number of days of data retention (must be greater
                than
                365 to create the yearly unique user visits period)<br>
                Default: <code>400</code><br>
                Data type: Integer
            </p>
            <p>
                <br>
                <strong>disabledDataTypes</strong> - Disabled data types from displaying on the view<br>
                Default:
            </p>
            <pre>[
    GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY,
    GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY,
    GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY,
    GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY,
    GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY,
    UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY,
    UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY,
]</pre>
            <p>Available Data types:</p>
            <pre>
    GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY
    GlobalFilter::DATA_TYPE_LOGINS_PER_DAY
    GlobalFilter::DATA_TYPE_VISITORS_PER_DAY
    GlobalFilter::DATA_TYPE_ACTIVITY_PER_DAY
    GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY
    GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY
    GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY
    GlobalFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY
    GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY
    GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY
    GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
    SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY
    SpaceFilter::DATA_TYPE_VISITORS_PER_DAY
    SpaceFilter::DATA_TYPE_ACTIVITY_PER_DAY
    SpaceFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
    SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE
    SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE
    SpaceFilter::DATA_TYPE_ACTIVITY_PER_SPACE
    UserFilter::DATA_TYPE_ACTIVITY_PER_DAY
    UserFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY
    UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY
    UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY
    UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
</pre>
            <p>Data type: Array</p>
            <p>
                <br>
                <strong>firstDayOfWeek</strong> - Force the first day of the week. If null, it's based on
                the
                language selected in the administration settings. 0 = Sunday, 1 = Monday<br>
                Default: <code>null</code><br>
                Data type: Integer | null
            </p>
        </div>
    </div>
</div>
