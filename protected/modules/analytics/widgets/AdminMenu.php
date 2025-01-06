<?php
/**
 * Survey
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;


class AdminMenu extends TabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AnalyticsModule.base', 'Globally'),
            'url' => ['/analytics/admin/index'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('analytics', 'admin', 'index'),
            'isVisible' => true,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AnalyticsModule.base', 'Spaces'),
            'url' => ['/analytics/admin/spaces'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('analytics', 'admin', 'spaces'),
            'isVisible' => true,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AnalyticsModule.base', 'Users'),
            'url' => ['/analytics/admin/users'],
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState('analytics', 'admin', 'users'),
            'isVisible' => true,
        ]));

        parent::init();
    }

}