<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\components\SettingsManager;
use Yii;
use yii\base\Model;

class Configuration extends Model
{
    public SettingsManager $settingsManager;
    /**
     * @var string
     */
    public $chartType = self::CHART_TYPE_AREA;

    public const CHART_TYPE_AREA = 'area';
    public const CHART_TYPE_LINE = 'line';
    public const CHART_TYPE_HISTOGRAM = 'histogram';
    public const CHART_TYPE_BAR = 'bar';


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chartType'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chartType' => Yii::t('AnalyticsModule.config', 'Chart type'),
        ];
    }

    public function loadBySettings(): void
    {
        $this->chartType = $this->settingsManager->get('chartType', $this->chartType);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('chartType', $this->chartType);

        return true;
    }

    public function getChartTypeLabels(): array
    {
        return [
            self::CHART_TYPE_AREA => Yii::t('AnalyticsModule.config', 'Area'),
            self::CHART_TYPE_LINE => Yii::t('AnalyticsModule.config', 'Line'),
            self::CHART_TYPE_HISTOGRAM => Yii::t('AnalyticsModule.config', 'Histogram'),
            self::CHART_TYPE_BAR => Yii::t('AnalyticsModule.config', 'Bars'),
        ];
    }
}
