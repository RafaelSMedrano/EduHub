<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics;

use DateTime;
use humhub\commands\CronController;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\analytics\models\AnalyticsLogins;
use humhub\modules\analytics\models\AnalyticsMembers;
use humhub\modules\analytics\models\AnalyticsReportedContent;
use humhub\modules\analytics\models\AnalyticsSpaceMembers;
use humhub\modules\analytics\models\AnalyticsSpaceVisits;
use humhub\modules\analytics\models\AnalyticsUser;
use humhub\modules\analytics\models\AnalyticsVisits;
use humhub\modules\analytics\permissions\ViewAnalytics;
use humhub\modules\analytics\permissions\ViewContainerAnalytics;
use humhub\modules\reportcontent\models\ReportContent;
use humhub\modules\reportmessage\models\ReportMessage;
use humhub\modules\rest\components\BaseController;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\HeaderControlsMenu;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\ProfileMenu;
use Throwable;
use Yii;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\AfterSaveEvent;
use yii\helpers\BaseConsole;
use yii\helpers\Url;
use yii\web\HttpException;


class Events
{
    public const CACHE_USER_PREFIX = 'a_u_';

    /**
     * @param $event
     * @return void
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function onAdminMenuInit($event)
    {
        /** @var AdminMenu $menu */
        $menu = $event->sender;

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        if (Yii::$app->user->can(ViewAnalytics::class)) { // Don't move in 'isVisible' as it doesn't work in all cases and because the "if" costs less
            $menu->addEntry(new MenuLink([
                'label' => $module->getName(),
                'icon' => $module->icon,
                'sortOrder' => 1500,
                'isActive' => MenuLink::isActiveState('analytics', 'admin'),
                'url' => Url::to(['/analytics/admin/index']),
                'isVisible' => true,
            ]));
        }
    }

    /**
     * @param $event
     * @return void
     */
    public static function onSpaceHeaderControlsMenuInit($event)
    {
        /** @var HeaderControlsMenu $headerMenu */
        $headerMenu = $event->sender;
        $space = $headerMenu->space;

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        if ($space->moduleManager->isEnabled('analytics') && $space->permissionManager->can(ViewContainerAnalytics::class)) { // Don't move in 'isVisible' as it doesn't work in all cases and because the "if" costs less
            $headerMenu->addEntry(new MenuLink([
                'label' => $module->getName(),
                'url' => $space->createUrl('/analytics/container-admin'),
                'icon' => $module->icon,
                'isActive' => MenuLink::isActiveState('analytics', 'container-admin'),
                'isVisible' => true,
                'sortOrder' => 500,
            ]));
        }
    }

    /**
     * @param Event $event
     * @return void
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function onProfileMenuInit(Event $event)
    {
        /* @var ProfileMenu $profileMenu */
        $profileMenu = $event->sender;
        $user = $profileMenu->user;

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        if ($user->moduleManager->isEnabled('analytics') && $user->permissionManager->can(ViewContainerAnalytics::class)) { // Don't move in 'isVisible' as it doesn't work in all cases and because the "if" costs less
            $profileMenu->addEntry(new MenuLink([
                'label' => $module->getName(),
                'url' => $user->createUrl('/analytics/container-admin'),
                'icon' => $module->icon,
                'isActive' => MenuLink::isActiveState('analytics', 'container-admin'),
                'isVisible' => true,
                'sortOrder' => 10000,
            ]));
        }
    }

    /**
     * @param ActionEvent $event
     * @return void
     */
    public static function onBeforeControllerAction(ActionEvent $event)
    {
        $controller = $event->action->controller;

        if (
            Yii::$app->user->isGuest
            || $controller->id === 'poll'
            || $controller instanceof BaseController // REST API request (even if from a module)
        ) {
            return;
        }

        if (
            !empty($controller->contentContainer)
            && $controller->contentContainer instanceof Space
        ) {
            $space = $controller->contentContainer;
            $cacheKey = Yii::$app->cache->buildKey(self::CACHE_USER_PREFIX . Yii::$app->user->id . '_sp_' . $space->id . '_' . date('d'));
            if (!Yii::$app->cache->exists($cacheKey)) {
                Yii::$app->cache->set($cacheKey, 1, static::nbSecondsRemainingToday());
                AnalyticsSpaceVisits::incrementDailyCount($space->contentcontainer_id);
            }
        } else {
            $cacheKey = Yii::$app->cache->buildKey(self::CACHE_USER_PREFIX . Yii::$app->user->id . '_' . date('d'));
            if (!Yii::$app->cache->exists($cacheKey)) {
                Yii::$app->cache->set($cacheKey, 1, static::nbSecondsRemainingToday());
                AnalyticsVisits::incrementDailyCount();
            }
        }

        if (
            !empty($_SERVER['HTTP_USER_AGENT'])
            && strpos('GuzzleHttp', $_SERVER['HTTP_USER_AGENT']) !== 0
        ) {
            $cacheKey = Yii::$app->cache->buildKey(self::CACHE_USER_PREFIX . Yii::$app->user->id . '_v_' . $_SERVER['HTTP_USER_AGENT']);
            $todayAnalyticsUserExists = Yii::$app->cache->getOrSet($cacheKey, function () {
                return AnalyticsUser::findOne([
                        'user_id' => Yii::$app->user->id,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'last_visit' => date('Y-m-d'),
                    ]) !== null;
            }, static::nbSecondsRemainingToday());

            if (!$todayAnalyticsUserExists) {
                AnalyticsUser::updateUserInfo(Yii::$app->user->id);
            }
        }
    }

    /**
     * Number of seconds remaining until tomorrow
     * @return int
     */
    protected static function nbSecondsRemainingToday()
    {
        $now = new DateTime();
        $tomorrow = new DateTime('tomorrow');
        $interval = $tomorrow->diff($now);
        return $interval->h * 3600 + $interval->m * 60 + $interval->s;
    }

    /**
     * If user log in, remove all previous actions in user_cleanup table
     * Thus, his account can again be automatically deactivated / deleted.
     * @param $event UserEvent
     */
    public static function onAfterLogin($event)
    {
        if (!isset($event->user)) {
            return;
        }

        $cacheKey = Yii::$app->cache->buildKey(self::CACHE_USER_PREFIX . $event->user->id . '_l_' . date('d'));
        if (!Yii::$app->cache->exists($cacheKey)) {
            Yii::$app->cache->set($cacheKey, 1, static::nbSecondsRemainingToday());
            AnalyticsLogins::incrementDailyCount();
        }
    }

    /**
     * @param $event
     * @return void
     */
    public static function onCronDailyRun($event)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');
        if (!$module) {
            return;
        }

        /** @var CronController $controller */
        $controller = $event->sender;

        $controller->stdout("Analytics module: saving new count records in database...");
        $nbRecords = AnalyticsLogins::dailySavePeriodsCount();
        $nbRecords += AnalyticsVisits::dailySavePeriodsCount();
        $nbRecords += AnalyticsMembers::dailySaveCount();
        $nbRecords += AnalyticsSpaceMembers::dailySaveCount();
        $controller->stdout('done (' . $nbRecords . ' records added)' . PHP_EOL, BaseConsole::FG_GREEN);

        $controller->stdout("Analytics module: removing old records in database...");
        $nbRecords = AnalyticsUser::deleteAll(['<', 'created_at', date('Y-m-d H:i:s', strtotime('-' . $module->nbDaysUserDataRetention . ' days'))]);
        $controller->stdout('done (' . $nbRecords . ' records deleted)' . PHP_EOL, BaseConsole::FG_GREEN);
    }

    /**
     * @param $event
     * @return void
     */
    public static function onModelSpaceBeforeDelete($event)
    {
        if (empty($event->sender)) {
            return;
        }

        /** @var Space $space */
        $space = $event->sender;

        AnalyticsSpaceVisits::deleteAll(['contentcontainer_id' => $space->contentcontainer_id]);
    }

    /**
     * @param $event
     * @return void
     */
    public static function onModelUserBeforeDelete($event)
    {
        if (empty($event->sender)) {
            return;
        }

        /** @var User $user */
        $user = $event->sender;

        AnalyticsUser::deleteAll(['user_id' => $user->id]);
    }

    public static function onReportContentAfterInsert(AfterSaveEvent $event): void
    {
        if ($event->sender instanceof ReportContent) {
            AnalyticsReportedContent::incrementTodayCount($event->sender);
        }
    }

    public static function onReportMessageAfterInsert(AfterSaveEvent $event): void
    {
        if ($event->sender instanceof ReportMessage) {
            AnalyticsReportedContent::incrementTodayCount(null, $event->sender);
        }
    }
}
