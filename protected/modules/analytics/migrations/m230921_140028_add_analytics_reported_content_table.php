<?php

use yii\db\Migration;

/**
 * Class m230921_140028_add_analytics_reported_content_table
 */
class m230921_140028_add_analytics_reported_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('analytics_reported_content', [
            'id' => $this->primaryKey(),
            'contentcontainer_id' => $this->integer(),
            'object_model' => $this->string(100),
            'created_by' => $this->integer(11)->notNull(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230921_140028_add_analytics_reported_content_table cannot be reverted.\n";

        return false;
    }
}
