<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\commands\CronController;
use humhub\components\Controller;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\analytics\Events;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\HeaderControlsMenu;
use humhub\modules\user\controllers\AuthController;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\ProfileMenu;
use yii\db\BaseActiveRecord;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'analytics',
    'class' => humhub\modules\analytics\Module::class,
    'namespace' => 'humhub\modules\analytics',
    'events' => [
        [
            'class' => AdminMenu::class,
            'event' => AdminMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAdminMenuInit']
        ],
        [
            'class' => HeaderControlsMenu::class,
            'event' => HeaderControlsMenu::EVENT_INIT,
            'callback' => [Events::class, 'onSpaceHeaderControlsMenuInit']
        ],
        [
            'class' => ProfileMenu::class,
            'event' => ProfileMenu::EVENT_BEFORE_RUN,
            'callback' => [Events::class, 'onProfileMenuInit']
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [Events::class, 'onCronDailyRun']
        ],
        [
            'class' => Controller::class,
            'event' => Controller::EVENT_BEFORE_ACTION,
            'callback' => [Events::class, 'onBeforeControllerAction']
        ],
        [
            'class' => AuthController::class,
            'event' => AuthController::EVENT_AFTER_LOGIN,
            'callback' => [Events::class, 'onAfterLogin']
        ],
        [
            'class' => Space::class,
            'event' => Space::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onModelSpaceBeforeDelete']
        ],
        [
            'class' => User::class,
            'event' => User::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onModelUserBeforeDelete']
        ],
        [
            'class' => 'humhub\modules\reportcontent\models\ReportContent',
            'event' => BaseActiveRecord::EVENT_AFTER_INSERT,
            'callback' => [Events::class, 'onReportContentAfterInsert']
        ],
        [
            'class' => 'humhub\modules\reportcontent\models\ReportMessage',
            'event' => BaseActiveRecord::EVENT_AFTER_INSERT,
            'callback' => [Events::class, 'onReportMessageAfterInsert']
        ],
    ],
];
?>