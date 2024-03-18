<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\analytics\widgets\GlobalAnalytics;
use humhub\modules\ui\view\components\View;

/**
 * @var $this View
 */
?>

    <p class="help-block"><?= Yii::t('AnalyticsModule.base', 'Usage statistics about the platform') ?></p>

<?= GlobalAnalytics::widget() ?>