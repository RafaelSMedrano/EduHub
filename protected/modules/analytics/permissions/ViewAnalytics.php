<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\permissions;

use humhub\modules\admin\components\BaseAdminPermission;
use Yii;

class ViewAnalytics extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $id = 'view_analytics';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'analytics';


    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('AnalyticsModule.config', 'View analytics');
        $this->description = Yii::t('AnalyticsModule.config', 'Allow user to view the statistics of this platform');
    }

}
