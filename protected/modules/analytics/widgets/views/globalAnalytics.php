<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\analytics\assets\ChartAssets;
use humhub\modules\analytics\models\filters\GlobalFilter;
use humhub\modules\analytics\Module;
use humhub\modules\comment\activities\NewComment;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\like\activities\Liked;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use yii\bootstrap\Alert;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $model GlobalFilter
 * @var $showForm bool
 * @var $showTotalNumbers bool
 * @var $showCharts bool
 * @var $chartDataUrl string
 */

$idPrefix = 'analytics-chart-global-';

ChartAssets::register($this);
$this->registerJsConfig('analytics.chart', [
    'startDate' => $model->startDate,
    'endDate' => $model->endDate,
    'dataTypes' => $model->dataTypes,
    'spaceGuids' => null,
    'idPrefix' => $idPrefix,
    'chartDataUrl' => $chartDataUrl,
]);

/** @var Module $module */
$module = Yii::$app->getModule('analytics');
$firstDayCountIfMissingRecords = $model->firstDayCountIfMissingRecords();
$privateMessagesCount = $model->privateMessagesCount();
$friendshipCount = $model->friendshipCount();
$reportedContentCount = $model->reportedContentCount();
?>

<?php if ($showForm) : ?>
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'startDate')->widget(DatePicker::class) ?>
    <?= $form->field($model, 'endDate')->widget(DatePicker::class) ?>
    <?= $form->field($model, 'dataTypes')->checkboxList($model->dataTypeLabels()) ?>
    <?= Button::save(Yii::t('AnalyticsModule.base', 'Filter'))->submit() ?>
    <?php ActiveForm::end(); ?>
    <br>
    <hr>
<?php endif; ?>

<?php if ($showTotalNumbers): ?>
    <h5><strong><?= Yii::t('AnalyticsModule.base', 'Total number (since the platform exists)') ?></strong></h5>
    <table class="table table-hover">
        <tr>
            <td><?= Yii::t('AnalyticsModule.base', 'Active user accounts') ?></td>
            <td><strong><?= GlobalFilter::nbTotalUsers() ?></strong></td>
        </tr>
        <tr>
            <td><?= Yii::t('AnalyticsModule.base', 'Content (posts and other modules)') ?></td>
            <td><strong><?= $model->activityCount(ContentCreated
                    ::class, false) ?></strong></td>
        </tr>
        <tr>
            <td><?= Yii::t('AnalyticsModule.base', 'Comments') ?></td>
            <td><strong><?= $model->activityCount(NewComment::class, false) ?></strong></td>
        </tr>
        <tr>
            <td><?= Yii::t('AnalyticsModule.base', '"Like" mention') ?></td>
            <td><strong><?= $model->activityCount(Liked::class, false) ?></strong></td>
        </tr>
        <?php if ($privateMessagesCount !== null): ?>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Private messages') ?></td>
                <td><strong><?= $privateMessagesCount ?></strong></td>
            </tr>
        <?php endif; ?>
        <?php if ($friendshipCount !== null): ?>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Friendships') ?></td>
                <td><strong><?= $friendshipCount ?></strong></td>
            </tr>
        <?php endif; ?>
        <?php if ($reportedContentCount !== null): ?>
            <tr>
                <td><?= Yii::t('AnalyticsModule.base', 'Reported content') ?></td>
                <td><strong><?= $reportedContentCount ?></strong></td>
            </tr>
        <?php endif; ?>
    </table>
<?php endif ?>

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
                    GlobalFilter::DATA_TYPE_LOGINS_PER_DAY,
                    GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY,
                    GlobalFilter::DATA_TYPE_VISITORS_PER_DAY,
                    GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY,
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