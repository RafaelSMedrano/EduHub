<?php

use yii\db\Migration;

/**
 * Class m210123_105300_initial
 */
class m210123_105300_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('analytics_user', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'user_agent' => $this->text(),
            'country' => $this->string(2),
            'language' => $this->string(255),
            'created_at' => $this->dateTime(),
            'last_visit' => $this->date(),
        ], '');

        $this->createTable('analytics_logins', [
            'id' => $this->primaryKey(),
            'start_date' => $this->date()->notNull(),
            'period' => $this->string(31)->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');

        $this->createTable('analytics_visits', [
            'id' => $this->primaryKey(),
            'start_date' => $this->date()->notNull(),
            'period' => $this->string(31)->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');

        $this->createTable('analytics_space_visits', [
            'id' => $this->primaryKey(),
            'contentcontainer_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');

        $this->createTable('analytics_members', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');

        $this->createTable('analytics_space_members', [
            'id' => $this->primaryKey(),
            'contentcontainer_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'count' => $this->integer()->notNull(),
        ], '');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210123_105300_initial cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210123_105300_initial cannot be reverted.\n";

        return false;
    }
    */
}
