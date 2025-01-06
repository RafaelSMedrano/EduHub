<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\components\ActiveRecord;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * @property int $id
 * @property int $contentcontainer_id
 * @property string $date
 * @property int $count
 */
abstract class BaseAnalyticsContainer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contentcontainer_id' => Yii::t('AnalyticsModule.base', 'Space'),
            'count' => Yii::t('AnalyticsModule.base', 'Number'),
            'date' => Yii::t('AnalyticsModule.base', 'Date'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contentcontainer_id', 'date', 'count'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['contentcontainer_id', 'count'], 'integer'],
        ];
    }

    /**
     * Compose automatically 'attributeTypes' according to `rules()` to maintain strict attribute types after model validation.
     * Avoid having changed attributes in `afterSave()` $changedAttributes if they are not string
     * https://www.yiiframework.com/doc/api/2.0/yii-behaviors-attributetypecastbehavior
     * If saving from a form and the value can be null, you need to typecast '' value to null in a beforeValidate() method
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ];
    }
}