<?php
/**
 * Survey
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\assets;

use humhub\components\assets\AssetBundle;


class ChartAssets extends AssetBundle
{
    public $sourcePath = '@analytics/resources';

    public $css = [
        'css/humhub.analytics.chart.css',
    ];

    public $js = [
        'js/apexcharts.min.js',
        'js/humhub.analytics.chart.js',
    ];
}