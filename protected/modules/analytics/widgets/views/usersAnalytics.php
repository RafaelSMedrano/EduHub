<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use donatj\UserAgent;
use humhub\libs\Html;
use humhub\libs\Iso3166Codes;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\analytics\assets\ChartAssets;
use humhub\modules\analytics\models\AnalyticsUser;
use humhub\modules\analytics\models\filters\UserFilter;
use humhub\modules\analytics\Module;
use humhub\modules\comment\activities\NewComment;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\like\activities\Liked;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\widgets\ProfileHeader;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\Button;
use yii\bootstrap\Alert;

/**
 * @var $this View
 * @var $model UserFilter
 * @var $showForm bool
 * @var $showUserPicker bool
 * @var $showProfileHeader bool
 * @var $showTotalNumbers bool
 * @var $showCharts bool
 * @var $showBrowsers bool
 * @var $analyticsUsers AnalyticsUser[]
 * @var $lastActivityDate string
 * @var $chartDataUrl string
 */

$idPrefix = 'analytics-chart-user-';

ChartAssets::register($this);
$this->registerJsConfig('analytics.chart', [
    'startDate' => $model->startDate,
    'endDate' => $model->endDate,
    'dataTypes' => $model->dataTypes,
    'userGuids' => $model->userGuids,
    'idPrefix' => $idPrefix,
    'chartDataUrl' => $chartDataUrl,
]);

$user = $model->getUser();
/** @var Module $module */
$module = Yii::$app->getModule('analytics');
$firstDayCountIfMissingRecords = $model->firstDayCountIfMissingRecords();
$privateMessagesCount = $model->privateMessagesCount();
$reportedContentCount = $model->reportedContentCount();
$friendshipCount = $model->friendshipCount();
?>

<?php if ($showForm): ?>
    <?php $form = ActiveForm::begin(); ?>
    <?php if ($showUserPicker): ?>
        <?= UserPickerField::widget([
            'maxSelection' => 1,
            'model' => $model,
            'form' => $form,
            'attribute' => 'userGuids',
        ]) ?>
    <?php endif ?>
    <?= $form->field($model, 'startDate')->widget(DatePicker::class) ?>
    <?= $form->field($model, 'endDate')->widget(DatePicker::class) ?>
    <?= $form->field($model, 'dataTypes')->checkboxList($model->dataTypeLabels()) ?>
    <?= Button::save(Yii::t('AnalyticsModule.base', 'Analyse'))->submit() ?>
    <?php ActiveForm::end(); ?>
    <br>
    <hr>
<?php endif ?>

<?php if ($user !== null) : ?>
    <?php if ($showProfileHeader) : ?>
        <?= ProfileHeader::widget(['user' => $user]) ?>
        <div>
            <?= Button::primary(Yii::t('AnalyticsModule.base', 'View profile'))->link($user->createUrl('/user/profile'))->icon('eye') ?>
            <?php if (Yii::$app->user->can(ManageUsers::class)) : ?>
                <?= Button::primary(Yii::t('AnalyticsModule.base', 'Edit account'))->link(['/admin/user/edit', 'id' => $user->id])->icon('edit') ?>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ($showTotalNumbers) : ?>
        <br>
        <h5><strong><?= Yii::t('AnalyticsModule.base', 'General information') ?></strong></h5>
        <table class="table table-striped table-hover">
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Account creation') ?></td>
                <td><strong><?= Yii::$app->formatter->asDatetime($user->created_at, 'medium') ?></strong></td>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Last login') ?></td>
                <td><strong><?= Yii::$app->formatter->asDatetime($user->last_login, 'medium') ?></strong>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Last activity (content, comment or "like")') ?></td>
                <td><strong><?= $lastActivityDate !== null ?
                            Yii::$app->formatter->asDatetime($lastActivityDate, 'medium') :
                            Yii::t('AnalyticsModule.base', 'Unknown') ?></strong></td>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Content (posts and other modules)') ?></td>
                <td><strong><?= $model->activityCount(ContentCreated::class) ?></strong></td>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Comments') ?></td>
                <td><strong><?= $model->activityCount(NewComment::class) ?></strong></td>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', '"Like" mention') ?></td>
                <td><strong><?= $model->activityCount(Liked::class) ?></strong></td>
            </tr>
            <?php if (Yii::$app->user->can(ManageUsers::class)) : ?>
                <?php if ($privateMessagesCount !== null): ?>
                    <tr>
                        <td><?= Yii::t('AnalyticsModule.base', 'Private messages') ?></td>
                        <td><strong><?= $privateMessagesCount ?></strong></td>
                    </tr>
                <?php endif; ?>
                <?php if ($reportedContentCount !== null): ?>
                    <tr>
                        <td><?= Yii::t('AnalyticsModule.base', 'Reported content') ?></td>
                        <td><strong><?= $reportedContentCount ?></strong></td>
                    </tr>
                <?php endif; ?>
                <?php if ($friendshipCount !== null): ?>
                    <tr>
                        <td><?= Yii::t('AnalyticsModule.base', 'Friends') ?></td>
                        <td><strong><?= $friendshipCount ?></strong></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Language') ?></td>
                <td><strong><?= Yii::$app->params['availableLanguages'][$user->language] ?? $user->language ?></strong>
                </td>
            </tr>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Time zone') ?></td>
                <td><strong><?= $user->time_zone ?></strong></td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if ($showCharts): ?>
        <br>
        <hr>
        <h5><strong><?= Yii::t('AnalyticsModule.base', 'From {startDate} to {endDate}', [
                    'startDate' => Yii::$app->formatter->asDate($model->startDate, 'medium'),
                    'endDate' => Yii::$app->formatter->asDate($model->endDate, 'medium'),
                ]) ?></strong></h5>
        <div id="analytics-charts-container">
            <?php foreach ($model->dataTypes as $dataType): ?>
                <br>
                <?= Html::tag('div', '', [
                'id' => $idPrefix . $dataType,
                'class' => 'analytics-chart-container analytics-chart-loader',
            ]) ?>

                <?php if (
                    $firstDayCountIfMissingRecords
                    && in_array($dataType, [
                        UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY,
                    ], true)
                ): ?>
                    <?= Alert::widget([
                        'options' => ['class' => 'alert-warning'],
                        'body' => Icon::get('exclamation-triangle') . ' ' . Yii::t('AnalyticsModule.base', 'The collection of statistical data only started on {dateFormatted}!', ['dateFormatted' => Yii::$app->formatter->asDate($firstDayCountIfMissingRecords, 'short')])
                    ]) ?>
                <?php endif; ?>
                <br>
            <?php endforeach; ?>
        </div>
    <?php endif ?>

    <?php if ($showTotalNumbers && Yii::$app->user->can(ManageUsers::class)) : ?>
        <br>
        <hr>
        <h5>
            <strong><?= Yii::t('AnalyticsModule.base', 'Browser(s) used since the last {nbDays} days', ['nbDays' => $module->nbDaysUserDataRetention]) ?></strong>
        </h5>
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th><?= Yii::t('AnalyticsModule.base', 'Browser') ?></th>
                <th><?= Yii::t('AnalyticsModule.base', 'Device') ?></th>
                <th><?= Yii::t('AnalyticsModule.base', 'Language') ?></th>
                <th><?= Yii::t('AnalyticsModule.base', 'Country') ?></th>
                <th><?= Yii::t('AnalyticsModule.base', 'Last visit') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($analyticsUsers) === 0) : ?>
                <tr>
                    <td colspan="5"><?= Yii::t('AnalyticsModule.base', 'No data') ?></td>
                </tr>
            <?php else: ?>
                <?php
                /** @var AnalyticsUser $analyticsUser */
                foreach ($analyticsUsers as $analyticsUser) : ?>
                    <tr>
                        <td>
                            <strong><?= $analyticsUser->getUserAgentInfo()[UserAgent\BROWSER] ?? '' ?> <?= $analyticsUser->getUserAgentInfo()[UserAgent\BROWSER_VERSION] ?? '' ?></strong>
                        </td>
                        <td>
                            <strong><?= ucfirst($analyticsUser->getUserAgentInfo()[UserAgent\PLATFORM] ?? '') ?></strong>
                        </td>
                        <td>
                            <strong><?= Yii::$app->params['availableLanguages'][$analyticsUser->language] ?? $analyticsUser->language ?></strong>
                        </td>
                        <td>
                            <strong><?= Iso3166Codes::country($analyticsUser->country) ?></strong>
                        </td>
                        <td>
                            <strong><?= Yii::$app->formatter->asDate($analyticsUser->last_visit, 'short') ?></strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif ?>