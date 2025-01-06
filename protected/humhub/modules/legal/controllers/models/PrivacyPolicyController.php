<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @since 0.5
 */
namespace humhub\modules\legal\controllers; 
use yii\web\Controller;

class PrivacyPolicyController extends Controller
{
    public function actionPrivacyPolicy()
    {
        return $this->render('privacyPolicy');
    }

}
