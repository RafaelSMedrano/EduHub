<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use Yii;

/**
 * @property int $id
 * @property int $contentcontainer_id
 * @property string $date
 * @property int $count
 */
class AnalyticsSpaceVisits extends BaseAnalyticsContainer
{
    const SESSION_KEY_PREFIX = 'analytics_visits_space_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_space_visits';
    }

    /**
     * @param int $contentContainerId
     * @return void
     */
    public static function incrementDailyCount($contentContainerId)
    {
        // In addition to the cache filter, check the session in case the cache has been flushed
        if (
            Yii::$app->user->isGuest
            || !Yii::$app->session->isActive
            || Yii::$app->session->get(self::SESSION_KEY_PREFIX . $contentContainerId) === date('Y-m-d')
        ) {
            return;
        }

        Yii::$app->session->set(self::SESSION_KEY_PREFIX . $contentContainerId, date('Y-m-d'));

        $analyticsSpaceVisits = static::findOne([
            'contentcontainer_id' => $contentContainerId,
            'date' => date('Y-m-d'),
        ]);
        if ($analyticsSpaceVisits !== null) {
            $analyticsSpaceVisits->count++;
        } else {
            $analyticsSpaceVisits = new AnalyticsSpaceVisits();
            $analyticsSpaceVisits->contentcontainer_id = $contentContainerId;
            $analyticsSpaceVisits->date = date('Y-m-d');
            $analyticsSpaceVisits->count = 1;
        }
        $analyticsSpaceVisits->save();
    }
}