<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models\filters;

use humhub\modules\activity\models\Activity;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\analytics\models\AnalyticsReportedContent;
use humhub\modules\analytics\Module;
use humhub\modules\content\components\ActiveQueryContent;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\mail\models\MessageEntry;
use humhub\modules\user\models\User;
use Throwable;
use Yii;

/**
 * @property int $id
 * @property int $created_at
 */
class UserFilter extends BaseFilter
{
    public const DATA_TYPE_ACTIVITY_PER_DAY = 'activity_per_day';
    public const DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY = 'inter_user_activity_per_day';
    public const DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY = 'inter_new_private_messages_per_day';
    public const DATA_TYPE_NEW_FRIENDSHIP_PER_DAY = 'inter_new_friendships_per_day';
    public const DATA_TYPE_REPORTED_CONTENT_PER_DAY = 'new_reported_content_per_day';


    /**
     * @var array
     */
    public $userGuids = [];

    /**
     * @var User|null
     */
    protected $_user;

    public function dataTypeLabels(): array
    {
        $dataTypeLabels = [
            self::DATA_TYPE_ACTIVITY_PER_DAY => Yii::t('AnalyticsModule.base', 'Activity (in spaces and user profiles), per day'),
            self::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY => Yii::t('AnalyticsModule.base', 'Activity with other users, per day'),
            self::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY => Yii::t('AnalyticsModule.base', 'New private messages, per day'),
            self::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY => Yii::t('AnalyticsModule.base', 'New friendships, per day'),
            self::DATA_TYPE_REPORTED_CONTENT_PER_DAY => Yii::t('AnalyticsModule.base', 'Reported content, per day'),
        ];

        if (Yii::$app->user->isGuest || !Yii::$app->user->can(ManageUsers::class)) {
            unset($dataTypeLabels[self::DATA_TYPE_REPORTED_CONTENT_PER_DAY]);
        }

        return array_diff_key($dataTypeLabels, array_flip($this->getDisabledDataTypes()));
    }

    public function getDisabledDataTypes()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        $disabledDataTypes = array_merge(
            $module->disabledDataTypes,
            $this->disabledDataTypes,
        );

        $nbInterUserActivityTypes = 2;
        if (!static::isFriendshipEnabled()) {
            $disabledDataTypes[] = self::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY;
            $nbInterUserActivityTypes--;
        }
        if (!static::isMessagerEnabled()) {
            $disabledDataTypes[] = self::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY;
            $nbInterUserActivityTypes--;
        }
        if ($nbInterUserActivityTypes === 0) {
            $disabledDataTypes[] = self::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY;
        }
        if (
            $nbInterUserActivityTypes === 1
            && !in_array(self::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY, $disabledDataTypes, true)
        ) {
            $disabledDataTypes[] = self::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY;
            if (static::isMessagerEnabled()) {
                $disabledDataTypes = array_diff($disabledDataTypes, [self::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY]);
            }
            if (static::isFriendshipEnabled()) {
                $disabledDataTypes = array_diff($disabledDataTypes, [self::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY]);
            }
        }
        if (!static::isReportContentEnabled()) {
            $disabledDataTypes[] = self::DATA_TYPE_REPORTED_CONTENT_PER_DAY;
        }

        return array_unique($disabledDataTypes);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['userGuids'], 'safe'],
        ]);
    }

    /**
     * @inerhitdoc
     */
    public function beforeValidate()
    {
        // Force array as loading may introduce string type
        $this->userGuids = $this->userGuids ? (array)$this->userGuids : [];
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userGuids' => Yii::t('AnalyticsModule.base', 'User'),
        ];
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = $this->userGuids ? User::findOne(['guid' => $this->userGuids]) : null;
        }
        return $this->_user;
    }

    /**
     * @param string|null $activityClass
     * @param bool $sinceStartDate
     * @return ActiveQueryContent
     */
    public function getActivityQuery(string $activityClass = null, bool $sinceStartDate = true)
    {
        if ($this->getUser() === null) {
            return null;
        }
        $query = Activity::find()
            ->joinWith(['content'])
            ->andWhere(['content.created_by' => $this->getUser()->id])
            ->orderBy(['content.created_at' => SORT_DESC]);

        if ($activityClass) {
            $query->andWhere(['activity.class' => $activityClass]);
        }

        if ($sinceStartDate) {
            $query->andWhere(['>=', 'content.created_at', $this->startDate]);
        }

        return $query;
    }

    /**
     * Number of members who visited the spaces since the starting day
     * @param string $activityClass
     * @return int
     * @throws Throwable
     */
    public function activityCount(string $activityClass)
    {
        $query = $this->getActivityQuery($activityClass);
        return $query === null ? 0 : (int)$query->count();
    }

    /**
     * @return int|null
     */
    public function privateMessagesCount()
    {
        if (
            !static::isMessagerEnabled()
            || $this->getUser() === null
        ) {
            return null;
        }
        return (int)MessageEntry::find()->where(['created_by' => $this->getUser()->id])->count();
    }

    /**
     * @return int|null
     */
    public function reportedContentCount()
    {
        if (
            !static::isReportContentEnabled()
            || $this->getUser() === null
        ) {
            return null;
        }

        return (int)AnalyticsReportedContent::find()
            ->where(['created_by' => $this->getUser()->id])
            ->sum('count');
    }

    /**
     * @return int|null
     */
    public function friendshipCount()
    {
        if (
            !static::isFriendshipEnabled()
            || $this->getUser() === null
        ) {
            return null;
        }
        return (int)Friendship::getFriendsQuery($this->getUser())->count();
    }
}