<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\components\ActiveRecord;
use humhub\modules\analytics\models\filters\GlobalFilter;
use humhub\modules\analytics\Module;
use IntlCalendar;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * @property int $id
 * @property string $start_date
 * @property string $period
 * @property int $count
 */
abstract class BaseAnalyticsPeriodic extends ActiveRecord
{
    public const PERIOD_DAY = 'day';
    public const PERIOD_WEEK = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_YEAR = 'year';

    public const SESSION_KEY_PREFIX = 'analytics_';

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'count' => Yii::t('AnalyticsModule.base', 'Number'),
            'start_date' => Yii::t('AnalyticsModule.base', 'Start date'),
            'period' => Yii::t('AnalyticsModule.base', 'Period'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_date', 'count'], 'required'],
            [['start_date'], 'date', 'format' => 'php:Y-m-d'],
            [['count'], 'integer'],
            [['period'], 'string', 'max' => 31],
        ];
    }

    /**
     * Compose automatically 'attributeTypes' according to `rules()` to maintain strict attribute types after model validation.
     * Avoid having changed attributes in `afterSave()` $changedAttributes if they are not string
     * https://www.yiiframework.com/doc/api/2.0/yii-behaviors-attributetypecastbehavior
     * If saving from a form and the value can be null, you need to typecast '' value to null in a beforeValidate() method
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ];
    }

    /**
     * @return void
     */
    public static function incrementDailyCount()
    {
        // In addition to the cache filter, check the session in case the cache has been flushed
        if (
            Yii::$app->user->isGuest
            || !Yii::$app->session->isActive
            || Yii::$app->session->get(self::SESSION_KEY_PREFIX . static::class) === date('Y-m-d')
        ) {
            return;
        }

        Yii::$app->session->set(self::SESSION_KEY_PREFIX . static::class, date('Y-m-d'));

        $record = static::findOne([
            'start_date' => date('Y-m-d'),
            'period' => self::PERIOD_DAY,
        ]);
        if ($record !== null) {
            $record->count++;
        } else {
            $record = new static();
            $record->start_date = date('Y-m-d');
            $record->period = self::PERIOD_DAY;
            $record->count = 1;
        }
        $record->save();
    }

    /**
     * @return int
     */
    public static function dailySavePeriodsCount()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        if ($module->firstDayOfWeek !== null) {
            $firstDayOfWeek = $module->firstDayOfWeek;
        } else {
            $locale = Yii::$app->settings->get('defaultLanguage');
            $cal1 = IntlCalendar::createInstance(NULL, $locale);
            $firstDayOfWeek = ($cal1->getFirstDayOfWeek() ?: 0) - 1;
        }

        $records = [];

        // Per Week
        if ((int)date('w') === $firstDayOfWeek) {
            $records[] = [
                'period' => self::PERIOD_WEEK,
                'startDate' => date('Y-m-d', strtotime('-1 week'))
            ];
        }

        // Per Month
        if ((int)date('j') === 1) {
            $records[] = [
                'period' => self::PERIOD_MONTH,
                'startDate' => date('Y-m-d', strtotime('-1 month'))
            ];

            // per Quarter
            if (in_array((int)date('n'), [1, 4, 7, 10])) {
                $records[] = [
                    'period' => self::PERIOD_QUARTER,
                    'startDate' => date('Y-m-d', strtotime('-3 month'))
                ];
            }

            // per Year
            if ((int)date('n') === 1) {
                $records[] = [
                    'period' => self::PERIOD_YEAR,
                    'startDate' => date('Y-m-d', strtotime('-1 year'))
                ];
            }
        }

        $nbRecords = 0;
        $moduleEnabledDate = $module->settings->get('moduleEnabledDate');
        foreach ($records as $record) {
            if (
                $record['startDate'] <= $moduleEnabledDate
                || static::findOne(['start_date' => $record['startDate'], 'period' => $record['period']]) !== null
            ) {
                continue;
            }

            $globalFilter = new GlobalFilter([
                'startDate' => $record['startDate'],
            ]);
            $logins = new static();
            $logins->start_date = $record['startDate'];
            $logins->period = $record['period'];
            $logins->count = static::class === AnalyticsLogins::class ?
                $globalFilter->nbMembersWhoLoggedIn() :
                $globalFilter->nbUsersWhoVisited();
            if ($logins->save()) {
                $nbRecords++;
            }
        }

        return $nbRecords;
    }
}