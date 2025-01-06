<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\controllers;

use humhub\components\Controller;
use humhub\modules\activity\models\Activity;
use humhub\modules\analytics\helpers\ApexChartsHelper;
use humhub\modules\analytics\models\AnalyticsLogins;
use humhub\modules\analytics\models\AnalyticsMembers;
use humhub\modules\analytics\models\AnalyticsReportedContent;
use humhub\modules\analytics\models\AnalyticsSpaceMembers;
use humhub\modules\analytics\models\AnalyticsSpaceVisits;
use humhub\modules\analytics\models\AnalyticsVisits;
use humhub\modules\analytics\models\filters\BaseFilter;
use humhub\modules\analytics\models\filters\GlobalFilter;
use humhub\modules\analytics\models\filters\SpaceFilter;
use humhub\modules\analytics\models\filters\UserFilter;
use humhub\modules\analytics\permissions\ViewAnalytics;
use humhub\modules\analytics\permissions\ViewContainerAnalytics;
use humhub\modules\comment\activities\NewComment;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\like\activities\Liked;
use humhub\modules\mail\models\MessageEntry;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Response;

class AjaxController extends Controller
{
    /**
     * @var string
     */
    protected $_startDate;

    /**
     * @var string
     */
    protected $_endDate;

    /**
     * @var null|array
     */
    protected $_contentContainerIds;

    /**
     * @var null|array
     */
    protected $_createdByIds;

    /**
     * @var array
     */
    protected $_series = [];

    /**
     * @var array
     */
    protected $_categories = [];

    /**
     * @var string
     */
    protected $_yAxisTitle = '';

    /**
     * @var string
     */
    protected $_yAxisType = ApexChartsHelper::X_AXIS_TYPE_CATEGORY;

    /**
     * @inerhitdoc
     */
    public function getAccessRules()
    {
        return [['login']];
    }

    /**
     * @return Response
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function actionChartData()
    {
        // If the user is allowed globally, he can see all data
        $canViewChartData = Yii::$app->user->can(ViewAnalytics::class);

        // Get values
        $dataType = Yii::$app->request->post('dataType');
        $this->_startDate = Yii::$app->request->post('startDate');
        $this->_endDate = Yii::$app->request->post('endDate');
        $spaceGuids = (array)Yii::$app->request->post('spaceGuids');
        $userGuids = (array)Yii::$app->request->post('userGuids');

        $globalDataTypeLabels = (new GlobalFilter())->dataTypeLabels();
        $spaceDataTypeLabels = (new SpaceFilter(['spaceGuids' => $spaceGuids]))->dataTypeLabels();
        $userDataTypeLabels = (new UserFilter(['userGuids' => $userGuids]))->dataTypeLabels();

        // If is from SpaceFilter
        if (array_key_exists($dataType, $spaceDataTypeLabels)) {
            $query = Space::find()->select('contentcontainer_id');
            if ($spaceGuids) {
                $this->_contentContainerIds = $query->where(['guid' => $spaceGuids])->column();
                if (!$canViewChartData) {
                    // If the user is not allowed globally, he can be allowed for the filtered containers
                    $canViewChartData = $this->canViewContainersChartData(Space::class, $spaceGuids);
                }
            } else {
                // If is from SpaceFilter and no space selected
                $this->_contentContainerIds = $query->visible()->column();
            }
        }
        // If is from UserFilter
        if ($userGuids && array_key_exists($dataType, $userDataTypeLabels)) {
            $this->_createdByIds = User::find()->where(['guid' => $userGuids])->column();
            if (!$canViewChartData) {
                // If the user is not allowed globally, he can be allowed for the filtered containers
                $canViewChartData = $this->canViewContainersChartData(User::class, $userGuids);
            }
        }

        if (!$canViewChartData) {
            $this->forbidden();
        }

        $activityClassToName = [];
        if (in_array($dataType, [
            GlobalFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY,
            UserFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY,
        ], true)) {
            if (BaseFilter::isMessagerEnabled()) {
                $activityClassToName[MessageEntry::class] = Yii::t('AnalyticsModule.base', 'New private messages');
            }
            if (BaseFilter::isFriendshipEnabled()) {
                $activityClassToName[Friendship::class] = Yii::t('AnalyticsModule.base', 'New friendships');
            }
        } else {
            $activityClassToName = [
                ContentCreated::class => Yii::t('AnalyticsModule.base', 'New content'),
                NewComment::class => Yii::t('AnalyticsModule.base', 'New comments'),
                Liked::class => Yii::t('AnalyticsModule.base', 'New "likes"'),
            ];
        }

        $this->initProperties($dataType, $activityClassToName);

        $this->createSeriesData($dataType, $activityClassToName);

        // Calculate total count and average and remove keys in series data arrays
        $totalCount = 0;
        $nbCounts = 0;
        foreach ($this->_series as $key => $seriesUnit) {
            $data = array_values($seriesUnit['data']);
            $this->_series[$key]['data'] = $data;
            foreach ($data as $count) {
                $totalCount += (int)$count;
                $nbCounts++;
            }
        }
        $average = $nbCounts ? round($totalCount / $nbCounts, 2) : null;

        $title = ($globalDataTypeLabels[$dataType] ?? '') ?: ($spaceDataTypeLabels[$dataType] ?? '');

        $subTitleElements = [];
        if ($average) {
            $subTitleElements[] = Yii::t('AnalyticsModule.base', 'Average: {averageCount}', ['averageCount' => $average]);
        }
        if (
            $totalCount
            && !in_array($dataType, [
                GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY,
                SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY,
            ])
        ) {
            $subTitleElements[] = Yii::t('AnalyticsModule.base', 'Total: {totalCount}', ['totalCount' => $totalCount]);
        }

        // Add info to subtitle
        switch ($dataType) {
            case GlobalFilter::DATA_TYPE_LOGINS_PER_DAY:
                $model = new GlobalFilter(['startDate' => $this->_startDate]);
                $subTitleElements[] = Yii::t('AnalyticsModule.base', '{nbUsers} users have logged into the platform since {startDate}', [
                    'nbUsers' => $model->nbMembersWhoLoggedIn(),
                    'startDate' => Yii::$app->formatter->asDate($model->startDate, 'short'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_VISITORS_PER_DAY:
                $model = new GlobalFilter(['startDate' => $this->_startDate]);
                $subTitleElements[] = Yii::t('AnalyticsModule.base', '{nbUsers} users visited the platform since the {startDate}', [
                    'nbUsers' => $model->nbUsersWhoVisited(),
                    'startDate' => Yii::$app->formatter->asDate($model->startDate, 'short'),
                ]);
                break;
            case SpaceFilter::DATA_TYPE_VISITORS_PER_DAY:
                $model = new SpaceFilter([
                    'spaceGuids' => $spaceGuids,
                    'startDate' => $this->_startDate,
                ]);
                $subTitleElements[] = Yii::t('AnalyticsModule.base', '{nbMembers} members visited the space(s) since the {startDate}', [
                    'nbMembers' => $model->nbMembersWhoVisited(),
                    'startDate' => Yii::$app->formatter->asDate($model->startDate, 'short'),
                ]);
                break;
        }

        // Generate array for Apex Chart
        $chartOptionsTypeBar = new ApexChartsHelper([
            'series' => $this->_series,
            'categories' => $this->_categories,
            'title' => $title,
            'subTitle' => implode(' · ', $subTitleElements),
            'yAxisTitle' => $this->_yAxisTitle,
            'xAxisType' => $this->_yAxisType,
        ]);

        return $this->asJson($chartOptionsTypeBar->chartOptionsTypeBar());
    }

    /**
     * @param string $containersClass Space::class or User::class
     * @param array $guids
     * @return bool
     * @throws InvalidConfigException
     */
    protected function canViewContainersChartData(string $containersClass, array $guids): bool
    {
        /** @var Space[]|User[] $containers */
        $containers = $containersClass::findAll(['guid' => $guids]);
        if (!$containers) {
            return false;
        }
        foreach ($containers as $container) {
            if (!$container->permissionManager->can(ViewContainerAnalytics::class)) {
                return false;
            }
        }
        return true;
    }

    protected function initProperties(string $dataType, array $activityClassToName): void
    {
        switch ($dataType) {
            case GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of user accounts'),
                ]);
                break;
            case SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of members'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_LOGINS_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of users'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_VISITORS_PER_DAY:
            case SpaceFilter::DATA_TYPE_VISITORS_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of visitors'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case GlobalFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY:
            case SpaceFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case UserFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case UserFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY:
                $this->initPerDayProperties(array_values($activityClassToName));
                break;
            case GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new content'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new comments'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new "likes"'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY:
            case UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new private messages'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY:
            case UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new friendships'),
                ]);
                break;
            case GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
            case SpaceFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
            case UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
                $this->initPerDayProperties([
                    Yii::t('AnalyticsModule.base', 'Number of new reported content'),
                ]);
                break;
            case SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE:
                $this->initPerSpaceProperties([
                    Yii::t('AnalyticsModule.base', 'Number of members'),
                ]);
                break;
            case SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE:
                $this->initPerSpaceProperties([
                    Yii::t('AnalyticsModule.base', 'Number of visitors'),
                ]);
                break;
            case SpaceFilter::DATA_TYPE_ACTIVITY_PER_SPACE:
                $this->initPerSpaceProperties(array_values($activityClassToName));
                break;
        }
    }

    /**
     * @param array $seriesNames
     * @return void
     * @throws InvalidConfigException
     */
    protected function initPerDayProperties(array $seriesNames)
    {
        $this->_yAxisType = ApexChartsHelper::X_AXIS_TYPE_DATETIME;
        $this->_yAxisTitle = Yii::t('AnalyticsModule.base', 'Number per day');

        $data = [];
        $date = $this->_startDate;
        while ($date <= $this->_endDate) {
            $data[$date] = 0;
            $this->_categories[] = $date;
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
        }

        foreach ($seriesNames as $key => $name) {
            $this->_series[$key] = [
                'name' => $name,
                'data' => $data,
            ];
        }
    }

    /**
     * @param array $seriesNames
     * @return void
     */
    protected function initPerSpaceProperties(array $seriesNames)
    {
        $this->_yAxisType = ApexChartsHelper::X_AXIS_TYPE_CATEGORY;
        $this->_yAxisTitle = Yii::t('AnalyticsModule.base', 'Number per space');

        $spaceQuery = Space::find()->andWhere(['contentcontainer_id' => $this->_contentContainerIds]);

        $data = [];
        /** @var Space $space */
        foreach ($spaceQuery->each() as $space) {
            $data[$space->contentcontainer_id] = 0;
            $this->_categories[] = $space->getDisplayName();
        }

        foreach ($seriesNames as $key => $name) {
            $this->_series[$key] = [
                'name' => $name,
                'data' => $data,
            ];
        }
    }

    protected function createSeriesData(string $dataType, array $activityClassToName): void
    {
        switch ($dataType) {
            case GlobalFilter::DATA_TYPE_LOGINS_PER_DAY:
            case GlobalFilter::DATA_TYPE_VISITORS_PER_DAY:
                $dataTypeToModelClass = [
                    GlobalFilter::DATA_TYPE_LOGINS_PER_DAY => AnalyticsLogins::class,
                    GlobalFilter::DATA_TYPE_VISITORS_PER_DAY => AnalyticsVisits::class,
                ];
                $modelClass = $dataTypeToModelClass[$dataType];
                $query = $modelClass::find()
                    ->where(['between', 'start_date',
                        $this->_startDate,
                        $this->_endDate
                    ])
                    ->andWhere(['period' => AnalyticsLogins::PERIOD_DAY])
                    ->orderBy(['start_date' => SORT_DESC]);
                /** @var AnalyticsLogins|AnalyticsVisits $record */
                foreach ($query->each(1000) as $record) {
                    $this->_series[0]['data'][$record->start_date] += (int)$record->count;
                }
                break;
            case GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY:
            case GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
            case SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY:
            case SpaceFilter::DATA_TYPE_VISITORS_PER_DAY:
            case SpaceFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
            case UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY:
                $dataTypeToModelClass = [
                    GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY => AnalyticsMembers::class,
                    GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY => AnalyticsReportedContent::class,
                    SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY => AnalyticsSpaceMembers::class,
                    SpaceFilter::DATA_TYPE_VISITORS_PER_DAY => AnalyticsSpaceVisits::class,
                    SpaceFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY => AnalyticsReportedContent::class,
                    UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY => AnalyticsReportedContent::class,
                ];
                $modelClass = $dataTypeToModelClass[$dataType];
                $query = $modelClass::find()
                    ->where(['between', 'date',
                        $this->_startDate,
                        $this->_endDate
                    ])
                    ->orderBy(['date' => SORT_DESC]);
                if ($this->_contentContainerIds !== null) {
                    $query->andWhere(['contentcontainer_id' => $this->_contentContainerIds]);
                }
                if ($this->_createdByIds !== null) {
                    $query->andWhere(['created_by' => $this->_createdByIds]);
                }
                /** @var AnalyticsMembers|AnalyticsSpaceMembers|AnalyticsSpaceVisits $record */
                foreach ($query->each(1000) as $record) {
                    $this->_series[0]['data'][$record->date] += (int)$record->count;
                }
                if (
                    $dataType === GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY
                    && isset($this->_series[0]['data'][date('Y-m-d')])
                ) {
                    // Today count may not still be available if daily cron not yet done
                    $this->_series[0]['data'][date('Y-m-d')] = GlobalFilter::nbTotalUsers();
                }
                // Convert to average
                $nContainers = $this->getNbContainers();
                if ($nContainers) {
                    foreach ($this->_series[0]['data'] as $date => $count) {
                        $this->_series[0]['data'][$date] = round($count / $nContainers, 2);
                    }
                    if (
                        $dataType === SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY
                        && isset($this->_series[0]['data'][date('Y-m-d')])
                    ) {
                        $spaceGuids = Space::find()
                            ->where(['contentcontainer_id' => $this->_contentContainerIds])
                            ->select('guid')
                            ->column();
                        $this->_series[0]['data'][date('Y-m-d')] = round(
                            (new SpaceFilter(['spaceGuids' => $spaceGuids]))->nbMembers() / $nContainers,
                            2);
                    }
                }
                break;
            case GlobalFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case GlobalFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY:
            case SpaceFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case UserFilter::DATA_TYPE_ACTIVITY_PER_DAY:
            case UserFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY:
                foreach (array_keys($activityClassToName) as $key => $activityClass) {
                    $query = $this->getActivityQuery($activityClass);
                    /** @var Activity|MessageEntry|Friendship $activity */
                    foreach ($query->each(1000) as $activity) {
                        $creationDate = $activity instanceof Activity ?
                            $activity->content->created_at :
                            $activity->created_at; // MessageEntry|Friendship
                        $this->_series[$key]['data'][date('Y-m-d', strtotime($creationDate))]++;
                    }
                    // Convert to average
                    $nContainers = $this->getNbContainers();
                    if ($nContainers) {
                        foreach ($this->_series[$key]['data'] as $date => $count) {
                            $this->_series[$key]['data'][$date] = round($count / $nContainers, 2);
                        }
                    }
                }
                break;
            case GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY:
            case GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY:
            case GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY:
            case GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY:
            case GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY:
            case UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY:
            case UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY:
                $dataTypeToActivityClass = [
                    GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY => ContentCreated::class,
                    GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY => NewComment::class,
                    GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY => Liked::class,
                    GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY => MessageEntry::class,
                    GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY => Friendship::class,
                    UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY => MessageEntry::class,
                    UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY => Friendship::class,
                ];
                $query = $this->getActivityQuery($dataTypeToActivityClass[$dataType]);
                /** @var Activity|MessageEntry|Friendship $activity */
                foreach ($query->each(1000) as $activity) {
                    $creationDate = $activity instanceof Activity ?
                        $activity->content->created_at :
                        $activity->created_at; // MessageEntry|Friendship
                    $this->_series[0]['data'][date('Y-m-d', strtotime($creationDate))]++;
                }
                break;
            case SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE:
            case SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE:
                $dataTypeToModelClass = [
                    SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE => AnalyticsSpaceMembers::class,
                    SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE => AnalyticsSpaceVisits::class,
                ];
                $modelClass = $dataTypeToModelClass[$dataType];
                $query = $modelClass::find()
                    ->where(['between', 'date',
                        $this->_startDate,
                        $this->_endDate
                    ])
                    ->orderBy(['date' => SORT_DESC])
                    ->andWhere(['contentcontainer_id' => $this->_contentContainerIds]);

                $spaceNbDaysWithCount = [];
                /** @var AnalyticsSpaceVisits $analyticsSpaceVisits */
                foreach ($query->each(1000) as $analyticsSpaceVisits) {
                    $contentcontainerId = $analyticsSpaceVisits->contentcontainer_id;
                    // Checking array key as the space could not be visible
                    if (array_key_exists($contentcontainerId, $this->_series[0]['data'])) {
                        $this->_series[0]['data'][$contentcontainerId] += (int)$analyticsSpaceVisits->count;
                        if (!array_key_exists($contentcontainerId, $spaceNbDaysWithCount)) {
                            $spaceNbDaysWithCount[$contentcontainerId] = 0;
                        }
                        $spaceNbDaysWithCount[$contentcontainerId]++;
                    }
                }

                // Daily average
                foreach ($this->_series[0]['data'] as $contentcontainerId => $spaceTotalVisits) {
                    $this->_series[0]['data'][$contentcontainerId] = round($spaceTotalVisits / ($spaceNbDaysWithCount[$contentcontainerId] ?? 1), 1);
                }
                break;
            case SpaceFilter::DATA_TYPE_ACTIVITY_PER_SPACE:
                foreach (array_keys($activityClassToName) as $key => $activityClass) {
                    $query = $this->getActivityQuery($activityClass);
                    /** @var Activity $record */
                    foreach ($query->each(1000) as $record) {
                        $contentcontainerId = $record->content->contentcontainer_id;
                        // Checking array key as the space could not be visible
                        if (array_key_exists($contentcontainerId, $this->_series[0]['data'])) {
                            $this->_series[$key]['data'][$contentcontainerId]++;
                        }
                    }
                }
                break;
        }
    }

    protected function getNbContainers(): ?int
    {
        if ($this->_contentContainerIds !== null) {
            return count($this->_contentContainerIds) ?: null;
        }
        if ($this->_createdByIds !== null) {
            return count($this->_createdByIds) ?: null;
        }
        return null;
    }

    /**
     * @param string $activityClass
     * @return \humhub\modules\content\components\ActiveQueryContent
     * @throws \Throwable
     */
    protected function getActivityQuery(string $activityClass)
    {
        if ($activityClass === Friendship::class) {
            $query = Friendship::find()
                ->where(['between', 'created_at',
                    $this->_startDate . ' 00:00:00',
                    $this->_endDate . ' 23:59:59'
                ])
                ->orderBy(['created_at' => SORT_DESC]);
            if ($this->_createdByIds !== null) {
                $query->andWhere(['user_id' => $this->_createdByIds]);
            }
            return $query;
        }

        if ($activityClass === MessageEntry::class) {
            $query = MessageEntry::find()
                ->where(['between', 'created_at',
                    $this->_startDate . ' 00:00:00',
                    $this->_endDate . ' 23:59:59'
                ])
                ->orderBy(['created_at' => SORT_DESC]);
            if ($this->_createdByIds !== null) {
                $query->andWhere(['created_by' => $this->_createdByIds]);
            }
            return $query;
        }

        $query = Activity::find()
            ->joinWith(['content'])
            ->where(['between', 'content.created_at',
                $this->_startDate . ' 00:00:00',
                $this->_endDate . ' 23:59:59'
            ])
            ->andWhere(['activity.class' => $activityClass])
            ->orderBy(['content.created_at' => SORT_DESC]);

        if ($this->_contentContainerIds !== null) {
            $query->andWhere(['content.contentcontainer_id' => $this->_contentContainerIds]);
        }

        if ($this->_createdByIds !== null) {
            $query->andWhere(['content.created_by' => $this->_createdByIds]);
        }

        return $query;
    }
}
