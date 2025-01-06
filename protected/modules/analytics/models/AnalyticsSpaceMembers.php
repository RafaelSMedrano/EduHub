<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\modules\analytics\models\filters\SpaceFilter;
use humhub\modules\space\models\Space;

/**
 * @property int $id
 * @property int $contentcontainer_id
 * @property string $date
 * @property int $count
 */
class AnalyticsSpaceMembers extends BaseAnalyticsContainer
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_space_members';
    }

    /**
     * @return int
     */
    public static function dailySaveCount()
    {
        if (static::findOne(['date' => date('Y-m-d')]) !== null) {
            return 0;
        }

        $nbRecords = 0;
        /** @var Space $space */
        foreach (Space::find()->where(['status' => Space::STATUS_ENABLED])->each() as $space) {
            $spaceMembers = new static;
            $spaceMembers->date = date('Y-m-d');
            $spaceMembers->contentcontainer_id = $space->contentcontainer_id;
            $spaceMembers->count = (new SpaceFilter(['spaceGuids' => [$space->guid]]))->nbMembers();
            if ($spaceMembers->save()) {
                $nbRecords++;
            }
        }
        return $nbRecords;
    }
}