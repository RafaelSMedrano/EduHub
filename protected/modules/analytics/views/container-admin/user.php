<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\analytics\models\filters\UserFilter;
use humhub\modules\analytics\widgets\UsersAnalytics;
use humhub\modules\ui\view\components\View;

/**
 * @var $this View
 * @var $userFilter UserFilter
 */
?>

<div class="panel">
    <div class="panel-heading">
        <strong><?= Yii::t('AnalyticsModule.base', 'Usage statistics') ?></strong>
    </div>
    <div class="panel-body">
        <?= UsersAnalytics::widget([
            'userFilter' => $userFilter,
            'showUserPicker' => false,
            'showProfileHeader' => false,
        ]) ?>
    </div>
</div>
