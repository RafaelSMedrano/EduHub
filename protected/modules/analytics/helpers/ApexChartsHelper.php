<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\helpers;

use humhub\modules\analytics\Module;
use Yii;
use yii\base\BaseObject;

class ApexChartsHelper extends BaseObject
{
    /**
     * https://apexcharts.com/docs/options/xaxis/#type
     */
    public const X_AXIS_TYPE_CATEGORY = 'category';
    public const X_AXIS_TYPE_DATETIME = 'datetime';
    public const X_AXIS_TYPE_NUMERIC = 'numeric';

    /**
     * @var array
     */
    public $series = [];

    /**
     * @var null|array
     */
    public $categories;

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $subTitle = '';

    /**
     * @var string  'category' | 'datetime' | 'numeric'
     */
    public $xAxisType = self::X_AXIS_TYPE_CATEGORY;

    /**
     * @var string
     */
    public $yAxisTitle = '';

    /**
     * @return array|null
     */
    public function chartOptionsTypeBar()
    {
        if (!$this->series) {
            return null;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');
        $model = $module->getConfiguration();

        return [
            'title' => [
                'text' => $this->title,
            ],
            'subtitle' => [
                'text' => $this->subTitle,
            ],
            'series' => $this->series, // See https://apexcharts.com/docs/series/
            'chart' => [
                'type' => $model->chartType,
                'toolbar' => [
                    'show' => true,
                    'autoSelected' => 'zoom'
                ],
                'height' => 350, // Should be equal in humhub.analytics.chart.css
                'width' => '100%',
                'stacked' => true,
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'xaxis' => [
                'type' => $this->xAxisType,
                'tickPlacement' => 'on', // for xAxisType: category, to allow zooming (see https://apexcharts.com/docs/zooming-in-category-x-axis/)
                'categories' => $this->categories ?? [],
            ],
            'yaxis' => [
                'title' => [
                    'text' => $this->yAxisTitle,
                ],
            ],
        ];
    }
}