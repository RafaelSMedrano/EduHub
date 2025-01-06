<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @since 0.5
 */

namespace humhub\modules\user\controllers; 
use yii\web\Controller;

class LegalController extends Controller
{

    public $layout = 'legalLayout';
    public function actionPrivacyPolicy()
    {
        return $this->render('privacyPolicy');
    }
    public function actionTermsOfUse()
    {
        return $this->render('privacyPolicy');
    }

}
