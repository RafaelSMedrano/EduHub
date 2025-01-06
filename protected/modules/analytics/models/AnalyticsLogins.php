<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

/**
 * @property int $id
 * @property string $start_date
 * @property string $period
 * @property int $count
 */
class AnalyticsLogins extends BaseAnalyticsPeriodic
{
    public const SESSION_KEY_PREFIX = 'analytics_logins_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_logins';
    }
}