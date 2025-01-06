<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models\filters;

use humhub\modules\activity\models\Activity;
use humhub\modules\analytics\models\AnalyticsReportedContent;
use humhub\modules\analytics\Module;
use humhub\modules\content\components\ActiveQueryContent;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Throwable;
use Yii;

/**
 * @property-read int[]|null $spaceIds
 */
class SpaceFilter extends BaseFilter
{
    /**
     * @var array
     */
    public $spaceGuids = [];
    /**
     * @var null|int[]
     */
    private $_spaceIds;
    /**
     * @var null|int[]
     */
    private $_contentContainerIds;


    public const DATA_TYPE_MEMBERS_PER_DAY = 'space_members_per_day';
    public const DATA_TYPE_VISITORS_PER_DAY = 'space_visits_per_day';
    public const DATA_TYPE_ACTIVITY_PER_DAY = 'space_activity_per_day';
    public const DATA_TYPE_REPORTED_CONTENT_PER_DAY = 'space_new_reported_content_per_day';
    public const DATA_TYPE_MEMBERS_PER_SPACE = 'members_per_space';
    public const DATA_TYPE_VISITORS_PER_SPACE = 'visitors_per_space';
    public const DATA_TYPE_ACTIVITY_PER_SPACE = 'activity_per_space';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['spaceGuids'], 'safe'],
        ]);
    }

    /**
     * @inerhitdoc
     */
    public function beforeValidate()
    {
        // Force array as loading may introduce string type
        $this->spaceGuids = $this->spaceGuids ? (array)$this->spaceGuids : [];
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'spaceGuids' => Yii::t('AnalyticsModule.base', 'Spaces'),
        ]);
    }

    public function dataTypeLabels(): array
    {
        $singleSpace = count($this->spaceGuids) === 1;
        $dataTypeLabels = [
            self::DATA_TYPE_MEMBERS_PER_DAY => ($singleSpace ?
                Yii::t('AnalyticsModule.base', 'Number of members, per day') :
                Yii::t('AnalyticsModule.base', 'Average number of members, per day')),
            self::DATA_TYPE_VISITORS_PER_DAY => ($singleSpace ?
                Yii::t('AnalyticsModule.base', 'Number of visitors (only logged in users), per day') :
                Yii::t('AnalyticsModule.base', 'Average number of visitors (only logged in users), per day')),
            self::DATA_TYPE_ACTIVITY_PER_DAY => ($singleSpace ?
                Yii::t('AnalyticsModule.base', 'Activity, per day') :
                Yii::t('AnalyticsModule.base', 'Average activity, per day')),
            self::DATA_TYPE_REPORTED_CONTENT_PER_DAY => ($singleSpace ?
                Yii::t('AnalyticsModule.base', 'Reported content, per day') :
                Yii::t('AnalyticsModule.base', 'Average number of reported content, per day')),
            self::DATA_TYPE_MEMBERS_PER_SPACE => Yii::t('AnalyticsModule.base', 'Average number (for the selected period) of members, per space'),
            self::DATA_TYPE_VISITORS_PER_SPACE => Yii::t('AnalyticsModule.base', 'Average number (for the selected period) of daily visits, per space'),
            self::DATA_TYPE_ACTIVITY_PER_SPACE => Yii::t('AnalyticsModule.base', 'Activity (new content, comments and "likes"), per space'),
        ];

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        return array_diff_key($dataTypeLabels, array_flip(array_merge(
            $module->disabledDataTypes,
            $this->disabledDataTypes,
        )));
    }

    /**
     * @return int[]|null
     */
    public function getSpaceIds()
    {
        if ($this->_spaceIds === null) {
            $this->_spaceIds = Space::find()
                ->where(['guid' => $this->spaceGuids])
                ->select('id')
                ->column();
        }
        return $this->_spaceIds;
    }

    /**
     * @return int[]|null
     */
    public function getContentContainerIds()
    {
        if ($this->_contentContainerIds === null) {
            $this->_contentContainerIds = Space::find()
                ->where(['guid' => $this->spaceGuids])
                ->select('contentcontainer_id')
                ->column();
        }
        return $this->_contentContainerIds;
    }

    /**
     * @return int
     */
    public function nbMembers()
    {
        return (int)Membership::find()
            ->where(['space_id' => $this->spaceIds, 'status' => Membership::STATUS_MEMBER])
            ->count();
    }

    /**
     * Number of members who visited the spaces since the starting day
     * @param bool $sinceStartDate
     * @return int
     */
    public function nbMembersWhoVisited(bool $sinceStartDate = true)
    {
        $query = Membership::find()
            ->where(['space_id' => $this->spaceIds, 'status' => Membership::STATUS_MEMBER]);
        if ($sinceStartDate) {
            $query->andWhere(['>=', 'last_visit', $this->startDate]);
        }
        return (int)$query->count();
    }

    /**
     * @param string $activityClass
     * @param bool $sinceStartDate
     * @return ActiveQueryContent
     * @throws Throwable
     */
    public function getActivityQuery(string $activityClass, bool $sinceStartDate = true)
    {
        $query = Activity::find()
            ->joinWith(['content'])
            ->andWhere(['activity.class' => $activityClass])
            ->orderBy(['content.created_at' => SORT_DESC]);

        if ($this->getContentContainerIds()) {
            $query->
            andWhere(['content.contentcontainer_id' => $this->getContentContainerIds()]);
        }

        if ($sinceStartDate) {
            $query->andWhere(['>=', 'content.created_at', $this->startDate]);
        }

        return $query;
    }

    /**
     * Number of members who visited the spaces since the starting day
     * @param string $activityClass
     * @param bool $sinceStartDate
     * @return int
     * @throws Throwable
     */
    public function activityCount(string $activityClass, bool $sinceStartDate = true)
    {
        return (int)$this->getActivityQuery($activityClass, $sinceStartDate)->count();
    }

    /**
     * @return int|null
     */
    public function reportedContentCount()
    {
        if (!static::isReportContentEnabled()) {
            return null;
        }

        $query = AnalyticsReportedContent::find();
        if ($contentContainerIds = $this->getContentContainerIds()) {
            $query->where(['contentcontainer_id' => $contentContainerIds]);
        }
        return (int)$query->sum('count');
    }
}