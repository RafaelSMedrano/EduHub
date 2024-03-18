<?php

use yii\db\Migration;

class uninstall extends Migration
{

    public function up()
    {
        $this->dropTable('analytics_user');
        $this->dropTable('analytics_logins');
        $this->dropTable('analytics_visits');
        $this->dropTable('analytics_space_visits');
        $this->dropTable('analytics_members');
        $this->dropTable('analytics_space_members');
        $this->dropTable('analytics_reported_content');
    }

    public function down()
    {
        echo "uninstall does not support migration down.\n";
        return false;
    }

}
