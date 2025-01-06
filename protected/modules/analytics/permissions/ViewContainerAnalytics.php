<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

class ViewContainerAnalytics extends BasePermission
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'analytics';

    /**
     * @inheritdoc
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        User::USERGROUP_SELF,
        User::USERGROUP_FRIEND,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_USER,
        User::USERGROUP_SELF,
        User::USERGROUP_GUEST
    ];

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('AnalyticsModule.config', 'View analytics');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('AnalyticsModule.config', 'Allow user to view the statistics');
    }
}