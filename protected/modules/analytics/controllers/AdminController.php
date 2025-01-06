<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\analytics\permissions\ViewAnalytics;

class AdminController extends Controller
{
    /**
     * @inerhitdoc
     */
    public $subLayout = '_subLayout';


    /**
     * Returns access rules for the standard access control behavior
     *
     * @return array the access permissions
     * @see AccessControl
     */
    public function getAccessRules()
    {
        return [
            ['permission' => ViewAnalytics::class]
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string
     */
    public function actionSpaces()
    {
        return $this->render('spaces');
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function actionUsers()
    {
        return $this->render('users');
    }
}