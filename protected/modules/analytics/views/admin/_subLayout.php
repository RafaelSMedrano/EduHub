<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\analytics\models\filters\GlobalFilter;
use humhub\modules\analytics\Module;
use humhub\modules\analytics\widgets\AdminMenu;
use humhub\modules\ui\view\components\View;
use humhub\modules\ui\view\helpers\ThemeHelper;
use humhub\widgets\FooterMenu;

/**
 * @var $this View
 * @var $model GlobalFilter
 * @var $missingDaysCount bool
 * @var $moduleEnabledDate string
 * @var $content string
 */

/** @var Module $module */
$module = Yii::$app->getModule('analytics');
?>

    <div class="container<?= ThemeHelper::isFluid() ? '-fluid' : '' ?>">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">

                    <?= \humhub\modules\admin\widgets\AdminMenu::widget(['template' => '@ui/menu/widgets/views/tab-menu.php']) ?>

                    <div>
                        <div class="panel-heading">
                            <strong><?= $module->getName() ?></strong>
                        </div>
                    </div>

                    <?= AdminMenu::widget() ?>

                    <div class="panel-body">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]) ?>