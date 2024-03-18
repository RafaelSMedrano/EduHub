<?php
/**
 * Analytics
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\analytics\models;

use humhub\modules\analytics\models\filters\BaseFilter;
use humhub\modules\reportcontent\models\ReportContent;
use humhub\modules\reportmessage\models\ReportMessage;

/**
 * @inheritdoc
 *
 * @property string $object_model
 * @property int $created_by
 */
class AnalyticsReportedContent extends BaseAnalyticsContainer
{
    public const EVENT_ON_REPORTED_OBJECT_RETRIEVAL = 'event_on_reported_object_retrieval';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'analytics_reported_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'date', 'count'], 'required'], // contentcontainer_id can be null
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['object_model'], 'string', 'max' => 100],
            [['contentcontainer_id', 'count', 'created_by'], 'integer'],
        ];
    }

    /**
     * @param ReportContent|null $reportContent
     * @param ReportMessage|null $reportMessage
     * @return bool
     */
    public static function incrementTodayCount($reportContent = null, $reportMessage = null): bool
    {
        if (!BaseFilter::isReportContentEnabled()) {
            return false;
        }

        $reportedObject = null;
        $createdBy = null;
        if ($reportContent) {
            $reportedObject = $reportContent->getContent()->one() ?? $reportContent->getComment()->one();
            $createdBy = $reportContent->created_by ?? null;
        } elseif ($reportMessage) {
            $reportedObject = $reportMessage->getMessageEntry()->one();
            $createdBy = $reportMessage->created_by ?? null;
        }
        if (!$reportedObject || !$createdBy) {
            return false;
        }

        $contentcontainerId = $reportedObject->contentcontainer_id ?? $reportedObject->content->contentcontainer_id;
        $reportedObjectModel = get_class($reportedObject);

        $analyticsReportedContent = static::findOne([
            'contentcontainer_id' => $contentcontainerId,
            'object_model' => $reportedObjectModel,
            'created_by' => $createdBy,
            'date' => date('Y-m-d'),
        ]);
        if ($analyticsReportedContent === null) {
            $analyticsReportedContent = new static();
            $analyticsReportedContent->contentcontainer_id = $contentcontainerId;
            $analyticsReportedContent->object_model = $reportedObjectModel;
            $analyticsReportedContent->created_by = $createdBy;
            $analyticsReportedContent->date = date('Y-m-d');
            $analyticsReportedContent->count = 0;
        }

        $analyticsReportedContent->count++;
        return $analyticsReportedContent->save();
    }
}