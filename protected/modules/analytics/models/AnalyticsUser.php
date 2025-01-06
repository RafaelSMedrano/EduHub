<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use donatj\UserAgent;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use humhub\components\ActiveRecord;
use humhub\modules\analytics\Module;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\helpers\StringHelper;

/**
 * @property int $id
 * @property int $user_id
 * @property string $user_agent
 * @property string $country
 * @property string $language
 * @property string $created_at
 * @property string $last_visit
 */
class AnalyticsUser extends ActiveRecord
{
    public $ipToGeoCode;

    protected $_userAgentInfo;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_user';
    }

    public static function updateUserInfo(?int $id)
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }

        $analyticsUser = static::findOne([
            'user_id' => Yii::$app->user->id,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ]);
        if ($analyticsUser === null) {
            $analyticsUser = new static();
            $analyticsUser->user_id = Yii::$app->user->id;
            $analyticsUser->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $analyticsUser->ipToGeoCode = $_SERVER['REMOTE_ADDR'] ?? '';
            $analyticsUser->language = StringHelper::byteSubstr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 255);
        }
        $analyticsUser->last_visit = date('Y-m-d');
        $analyticsUser->save();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('AnalyticsModule.base', 'User'),
            'user_agent' => Yii::t('AnalyticsModule.base', 'Browser'),
            'country' => Yii::t('AnalyticsModule.base', 'Country'),
            'language' => Yii::t('AnalyticsModule.base', 'Language'),
            'created_at' => Yii::t('AnalyticsModule.base', 'Created at'),
            'last_visit' => Yii::t('AnalyticsModule.base', 'Last visit'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_agent'], 'required'],
            [['user_id'], 'integer'],
            [['user_agent'], 'string'],
            [['country'], 'string', 'max' => 2],
            [['language'], 'string', 'max' => 255],
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

    /**
     * @return string[] an array with 'browser', 'version' and 'platform' keys
     */
    public function getUserAgentInfo()
    {
        if ($this->_userAgentInfo === null) {
            if (!class_exists('UserAgent')) {
                require_once Yii::getAlias('@analytics/vendor/autoload.php');
            }
            $this->_userAgentInfo = UserAgent\parse_user_agent($this->user_agent);
        }

        return $this->_userAgentInfo;
    }

    public function beforeSave($insert)
    {
        if ($this->ipToGeoCode && !$this->country) {
            $this->country = $this->parseCountryFromIp();
        }
        $languages = explode(',', $this->language);
        if (count($languages) > 1) {
            $this->language = reset($languages);
        }
        return parent::beforeSave($insert);
    }

    /**
     * https://maxmind.github.io/GeoIP2-php/
     * @return string|null
     */
    public function parseCountryFromIp()
    {
        if (!class_exists('Reader')) {
            require_once Yii::getAlias('@analytics/vendor/autoload.php');
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('analytics');

        try {
            $countryReader = new Reader($module->basePath . '/resources/geoIpDB/GeoLite2-Country.mmdb');
            $record = $countryReader->country($this->ipToGeoCode);
            return $record->country->isoCode;
        } catch (AddressNotFoundException $e) {
        } catch (InvalidDatabaseException $e) {
            Yii::error('Error geolocalizing user from IP address. Invalid Database: ' . $e, 'analytics');
        }
        return null;
    }
}