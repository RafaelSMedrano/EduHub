<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\analytics\widgets\UsersAnalytics;
use humhub\modules\ui\view\components\View;

/**
 * @var $this View
 */
?>

    <p class="help-block"><?= Yii::t('AnalyticsModule.base', 'Analysis of a specific user') ?></p>

<?= UsersAnalytics::widget() ?>