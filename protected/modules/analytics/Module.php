<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics;

use humhub\modules\analytics\models\Configuration;
use humhub\modules\analytics\models\filters\GlobalFilter;
use humhub\modules\analytics\models\filters\UserFilter;
use humhub\modules\analytics\permissions\ViewAnalytics;
use humhub\modules\analytics\permissions\ViewContainerAnalytics;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

class Module extends ContentContainerModule
{
    /**
     * @var string defines the icon
     */
    public $icon = 'line-chart';

    /**
     * @var string defines path for resources, including the screenshots path for the marketplace
     */
    public $resourcesPath = 'resources';

    /**
     * Number of days of data retention (must be greater than 365 to create the yearly unique user visits period)
     * @var int
     */
    public $nbDaysUserDataRetention = 400;

    /**
     * @var array Disabled data types from displaying on the view
     * Available Data types:
     * GlobalFilter::DATA_TYPE_ACCOUNTS_PER_DAY
     * GlobalFilter::DATA_TYPE_LOGINS_PER_DAY
     * GlobalFilter::DATA_TYPE_VISITORS_PER_DAY
     * GlobalFilter::DATA_TYPE_ACTIVITY_PER_DAY
     * GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY
     * GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY
     * GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY
     * GlobalFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY
     * GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY
     * GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY
     * GlobalFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
     * SpaceFilter::DATA_TYPE_MEMBERS_PER_DAY
     * SpaceFilter::DATA_TYPE_VISITORS_PER_DAY
     * SpaceFilter::DATA_TYPE_ACTIVITY_PER_DAY
     * SpaceFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
     * SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE
     * SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE
     * SpaceFilter::DATA_TYPE_ACTIVITY_PER_SPACE
     * UserFilter::DATA_TYPE_ACTIVITY_PER_DAY
     * UserFilter::DATA_TYPE_INTER_USER_ACTIVITY_PER_DAY
     * UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY
     * UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY
     * UserFilter::DATA_TYPE_REPORTED_CONTENT_PER_DAY
     */
    public $disabledDataTypes = [
        GlobalFilter::DATA_TYPE_NEW_CONTENT_PER_DAY,
        GlobalFilter::DATA_TYPE_NEW_COMMENTS_PER_DAY,
        GlobalFilter::DATA_TYPE_NEW_LIKES_PER_DAY,
        GlobalFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY,
        GlobalFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY,
        UserFilter::DATA_TYPE_NEW_PRIVATE_MESSAGES_PER_DAY,
        UserFilter::DATA_TYPE_NEW_FRIENDSHIP_PER_DAY,
    ];

    /**
     * Force the first day of the week
     * If null, it's based on the language selected in the administration settings
     * @var null|int 0 = Sunday, 1 = Monday
     */
    public $firstDayOfWeek;

    private ?Configuration $_configuration = null;

    public function getConfiguration(): Configuration
    {
        if ($this->_configuration === null) {
            $this->_configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->_configuration->loadBySettings();
        }
        return $this->_configuration;
    }

    /**
     * @inerhitdoc
     */
    public function getName()
    {
        return Yii::t('AnalyticsModule.base', 'Analytics');
    }

    /**
     * @inerhitdoc
     */
    public function getDescription()
    {
        return Yii::t('AnalyticsModule.base', 'Statistics about Humhub usage (globally, in spaces and about users).');
    }

    /**
     * @inerhitdoc
     */
    public function enable()
    {
        $enabled = parent::enable();

        if ($enabled) {
            /** @var Module $module */
            $module = Yii::$app->getModule('analytics');
            $module->settings->set('moduleEnabledDate', date('Y-m-d'));

            ContentContainerModuleManager::setDefaultState(Space::class, 'analytics', ContentContainerModuleState::STATE_ENABLED);
            ContentContainerModuleManager::setDefaultState(User::class, 'analytics', ContentContainerModuleState::STATE_ENABLED);
        }

        return $enabled;
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [Space::class, User::class];
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/analytics/config']);
    }


    /**
     * @inerhitdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer === null) {
            return [
                new ViewAnalytics(),
            ];
        }

        return [
            new ViewContainerAnalytics(),
        ];
    }
}
