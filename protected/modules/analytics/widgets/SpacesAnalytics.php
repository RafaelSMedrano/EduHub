<?php
/**
 * Survey
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\widgets;

use humhub\components\Widget;
use humhub\modules\analytics\models\filters\SpaceFilter;
use Yii;
use yii\helpers\Url;


class SpacesAnalytics extends Widget
{
    public ?SpaceFilter $spaceFilter = null;
    public bool $showForm = true;
    public bool $showSpacePicker = true;
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
        $this->spaceFilter = $this->spaceFilter ?: new SpaceFilter();
        if ($this->spaceFilter->load(Yii::$app->request->post())) {
            $this->spaceFilter->validate();
        }

        return $this->render('spacesAnalytics', [
            'model' => $this->spaceFilter,
            'showForm' => $this->showForm,
            'showSpacePicker' => $this->showSpacePicker,
            'showTotalNumbers' => $this->showTotalNumbers,
            'showCharts' => $this->showCharts,
            'chartDataUrl' => Url::to([$this->chartDataRoute]),
        ]);
    }
}