<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\modules\analytics\models\filters\GlobalFilter;

class AnalyticsMembers extends BaseAnalytics
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_members';
    }

    /**
     * @return int
     */
    public static function dailySaveCount()
    {
        if (static::findOne(['date' => date('Y-m-d')]) !== null) {
            return 0;
        }

        $members = new static;
        $members->date = date('Y-m-d');
        $members->count = GlobalFilter::nbTotalUsers();
        if ($members->save()) {
            return 1;
        }
        return 0;
    }
}