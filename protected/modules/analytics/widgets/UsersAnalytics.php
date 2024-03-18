<?php
/**
 * Survey
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\widgets;

use humhub\components\Widget;
use humhub\modules\activity\models\Activity;
use humhub\modules\analytics\models\AnalyticsUser;
use humhub\modules\analytics\models\filters\UserFilter;
use Yii;
use yii\helpers\Url;


class UsersAnalytics extends Widget
{
    public ?UserFilter $userFilter = null;
    public bool $showForm = true;
    public bool $showUserPicker = true;
    public bool $showProfileHeader = true;
    public bool $showTotalNumbers = true;
    public bool $showCharts = true;
    public bool $showBrowsers = true;

    /**
     * Route used for searching chart data
     * @var string
     */
    public string $chartDataRoute = '/analytics/ajax/chart-data';

    /**
     * @inerhitdoc
     * @throws \Throwable
     */
    public function run()
    {
        $this->userFilter = $this->userFilter ?: new UserFilter();
        if ($this->userFilter->load(Yii::$app->request->post())) {
            $this->userFilter->validate();
        }

        if ($this->userFilter->getUser() !== null) {
            /** @var Activity $lastActivity */
            $lastActivity = $this->userFilter->getActivityQuery()->one();
            $lastActivityDate = $lastActivity !== null ?
                $lastActivity->content->created_at :
                null;

            $analyticsUsers = AnalyticsUser::find()
                ->where(['user_id' => $this->userFilter->getUser()->id])
                ->orderBy(['last_visit' => SORT_DESC])
                ->all();
        }

        return $this->render('usersAnalytics', [
            'model' => $this->userFilter,
            'showForm' => $this->showForm,
            'showUserPicker' => $this->showUserPicker,
            'showProfileHeader' => $this->showProfileHeader,
            'showTotalNumbers' => $this->showTotalNumbers,
            'showCharts' => $this->showCharts,
            'showBrowsers' => $this->showBrowsers,
            'lastActivityDate' => $lastActivityDate ?? '',
            'analyticsUsers' => $analyticsUsers ?? [],
            'chartDataUrl' => Url::to([$this->chartDataRoute]),
        ]);
    }
}