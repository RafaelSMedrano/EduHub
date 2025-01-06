<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\controllers;

use humhub\modules\analytics\models\filters\SpaceFilter;
use humhub\modules\analytics\models\filters\UserFilter;
use humhub\modules\analytics\permissions\ViewContainerAnalytics;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class ContainerAdminController extends ContentContainerController
{
    public $clearLayout = false;

    /**
     * @return string
     */
    public function actionIndex()
    {
        if (!$this->contentContainer->permissionManager->can(ViewContainerAnalytics::class)) {
            $this->forbidden();
        }

        if ($this->contentContainer instanceof Space) {
            return $this->renderSpaceAnalytics();
        }

        if ($this->contentContainer instanceof User) {
            return $this->renderUserAnalytics();
        }
    }

    protected function renderSpaceAnalytics(): string
    {
        $spaceFilter = new SpaceFilter([
            'disabledDataTypes' => [
                SpaceFilter::DATA_TYPE_MEMBERS_PER_SPACE,
                SpaceFilter::DATA_TYPE_VISITORS_PER_SPACE,
                SpaceFilter::DATA_TYPE_ACTIVITY_PER_SPACE,
            ],
            'spaceGuids' => [$this->contentContainer->guid],
        ]);

        return $this->render('space', [
            'spaceFilter' => $spaceFilter,
            'inContainer' => true,
        ]);
    }

    private function renderUserAnalytics(): string
    {
        $userFilter = new UserFilter([
            'userGuids' => [$this->contentContainer->guid],
        ]);

        return $this->render('user', [
            'userFilter' => $userFilter,
            'inContainer' => true,
        ]);
    }
}