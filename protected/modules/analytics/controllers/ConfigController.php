<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\analytics\Module;
use Yii;


/**
 * ConfigController is the module form configuration
 * For administrators only
 */
class ConfigController extends Controller
{
    /**
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var Module $module */
        $module = $this->module;
        $model = $module->getConfiguration();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }
}