<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models\filters;

use humhub\libs\DbDateValidator;
use humhub\modules\analytics\Module;
use Yii;
use yii\base\Model;
use yii\behaviors\AttributeTypecastBehavior;

abstract class BaseFilter extends Model
{
    /**
     * @var string
     */
    public $startDate;
    /**
     * @var string
     */
    public $endDate;
    /**
     * @var array
     */
    public $dataTypes;
    /**
     * @var array
     */
    public $disabledDataTypes = [];

    /**
     * @inerhitdoc
     */
    public function init()
    {
        parent::init();

        if ($this->startDate === null) {
            $this->startDate = date('Y-m-d', strtotime("-1 month"));
        }
        if ($this->endDate === null) {
            $this->endDate = date('Y-m-d');
        }
        if ($this->dataTypes === null) {
            $this->dataTypes = array_keys($this->dataTypeLabels());
        }
    }

    public function dataTypeLabels(): array
    {
        return [];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['startDate', 'endDate'], DbDateValidator::class, 'convertToFormat' => 'Y-m-d'],
            [['dataTypes'], 'safe'],
        ];
    }

    /**
     * Compose automatically 'attributeTypes' according to `rules()` to maintain strict attribute types after model validation.
     * Avoid having unchanged attributes in `afterSave()` $changedAttributes if they are not string
     * https://www.yiiframework.com/doc/api/2.0/yii-behaviors-attributetypecastbehavior
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
     * @inerhitdoc
     */
    public function beforeValidate()
    {
        // Force array as loading may introduce string type
        $this->dataTypes = $this->dataTypes ? (array)$this->dataTypes : [];
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'startDate' => Yii::t('AnalyticsModule.base', 'Start date'),
            'endDate' => Yii::t('AnalyticsModule.base', 'End date'),
            'dataTypes' => Yii::t('AnalyticsModule.base', 'Data types'),
        ];
    }

    /**
     * @return string|null
     */
    public function firstDayCountIfMissingRecords()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');
        $moduleEnabledDate = $module->settings->get('moduleEnabledDate');
        $missingRecords = (
            $moduleEnabledDate !== null
            && $this->startDate < $moduleEnabledDate
        );
        return $missingRecords ? $moduleEnabledDate : null;
    }

    public static function isFriendshipEnabled(): bool
    {
        /** @var \humhub\modules\friendship\Module $friendshipModule */
        $friendshipModule = Yii::$app->getModule('friendship');
        return $friendshipModule->getIsEnabled();
    }

    public static function isMessagerEnabled(): bool
    {
        return Yii::$app->hasModule('mail');
    }

    public static function isReportContentEnabled(): bool
    {
        return Yii::$app->hasModule('reportcontent');
    }
}