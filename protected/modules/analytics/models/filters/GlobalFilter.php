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
use humhub\modules\analytics\models\AnalyticsUser;
use humhub\modules\analytics\Module;
use humhub\modules\content\components\ActiveQueryContent;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\mail\models\MessageEntry;
use humhub\modules\user\models\User;
use Throwable;
use Yii;

class GlobalFilter extends BaseFilter
{
    public const DATA_TYPE_ACCOUNTS_PER_DAY = 'accounts_per_day';
    public const DATA_TYPE_LOGINS_PER_DAY = 'logins_per_day';
    public const DATA_TYPE_VISITORS_PER_DAY = 'visitors_per_day';
    public const DATA_TYPE_ACTIVITY_PER_DAY = 'activity_per_day';
    public const DATA_TYPE_NEW_CONTENT_PER_DAY = 'new_content_per_day';
    public const DATA_TYPE_NEW_COMMENTS_PER_DAY = 'new_comments_per_day';
    public const DATA_TYPE_NEW_LIKES_PER_DAY = 'new_likes_per_day';
    public const DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY = 'inter_user_activity_per_day';
    public const DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY = 'inter_new_private_messages_per_day';
    public const DATA_TYPE_NEW_FRIENDSHIP_PER_DAY = 'inter_new_friendships_per_day';
    public const DATA_TYPE_REPORTED_CONTENT_PER_DAY = 'new_reported_content_per_day';

    public function dataTypeLabels(): array
    {
        $dataTypeLabels = [
            self::DATA_TYPE_ACCOUNTS_PER_DAY => Yii::t('AnalyticsModule.base', 'Number of active user accounts, per day'),
            self::DATA_TYPE_LOGINS_PER_DAY => Yii::t('AnalyticsModule.base', 'Number of users who logged in, per day'),
            self::DATA_TYPE_VISITORS_PER_DAY => Yii::t('AnalyticsModule.base', 'Number of users who visited the platform, per day'),
            self::DATA_TYPE_ACTIVITY_PER_DAY => Yii::t('AnalyticsModule.base', 'Activity (in spaces and user profiles), per day'),
            self::DATA_TYPE_NEW_CONTENT_PER_DAY => Yii::t('AnalyticsModule.base', 'New content, per day'),
            self::DATA_TYPE_NEW_COMMENTS_PER_DAY => Yii::t('AnalyticsModule.base', 'New comments, per day'),
            self::DATA_TYPE_NEW_LIKES_PER_DAY => Yii::t('AnalyticsModule.base', 'New "likes", per day'),
            self::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY => Yii::t('AnalyticsModule.base', 'Activity between users, per day'),
            self::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY => Yii::t('AnalyticsModule.base', 'New private messages, per day'),
            self::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY => Yii::t('AnalyticsModule.base', 'New friendships, per day'),
            self::DATA_TYPE_REPORTED_CONTENT_PER_DAY => Yii::t('AnalyticsModule.base', 'Reported content, per day'),
        ];

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
     * Number of members who logged in the platform since the starting day
     * @return int
     */
    public function nbMembersWhoLoggedIn()
    {
        return (int)User::find()
            ->where(['>=', 'last_login', $this->startDate])
            ->count();
    }

    /**
     * Number of users who visited the platform since the starting day
     * @return int
     */
    public function nbUsersWhoVisited()
    {
        return (int)AnalyticsUser::find()
            ->where(['>=', 'last_visit', $this->startDate])
            ->select('user_id')
            ->distinct()
            ->count();
    }

    /**
     * @return int
     */
    public static function nbTotalUsers()
    {
        return (int)User::find()->active()->count();
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

        if ($sinceStartDate) {
            $query->andWhere(['>=', 'content.created_at', $this->startDate]);
        }

        return $query;
    }

    /**
     * Number of members who visited the platform since the starting day
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
    public function privateMessagesCount()
    {
        if (!static::isMessagerEnabled()) {
            return null;
        }
        return (int)MessageEntry::find()->count();
    }

    /**
     * @return int|null
     */
    public function friendshipCount()
    {
        if (!static::isFriendshipEnabled()) {
            return null;
        }
        return (int)Friendship::find()->count();
    }

    /**
     * @return int|null
     */
    public function reportedContentCount()
    {
        if (!static::isReportContentEnabled()) {
            return null;
        }
        return (int)AnalyticsReportedContent::find()->sum('count');
    }
}