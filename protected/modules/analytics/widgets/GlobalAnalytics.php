<?php
/**
 * Survey
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\widgets;

use humhub\components\Widget;
use humhub\modules\analytics\models\filters\GlobalFilter;
use Yii;
use yii\helpers\Url;


class GlobalAnalytics extends Widget
{
    public ?GlobalFilter $globalFilter = null;
    public bool $showForm = true;
    public bool $showTotalNumbers = true;
    public bool $showCharts = true;

    /**
     * Route used for searching chart data
     * @var string
     */
    public string $chartDataRoute = '/analytics/ajax/chart-data';

    /**
     * @inerhitdoc
     */
    public function run()
    {
        $this->globalFilter = $this->globalFilter ?: new GlobalFilter();
        if ($this->globalFilter->load(Yii::$app->request->post())) {
            $this->globalFilter->validate();
        }

        return $this->render('globalAnalytics', [
            'model' => $this->globalFilter,
            'showForm' => $this->showForm,
            'showTotalNumbers' => $this->showTotalNumbers,
            'showCharts' => $this->showCharts,
            'chartDataUrl' => Url::to([$this->chartDataRoute]),
        ]);
    }
}