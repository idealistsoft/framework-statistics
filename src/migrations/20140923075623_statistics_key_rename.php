<?php

use Phinx\Migration\AbstractMigration;

class StatisticsKeyRename extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM Statistics');

        foreach ($rows as $row) {
            $metric = $row['metric'];
            $metric = str_replace('-', '_', $metric);

            $this->execute('UPDATE Statistics SET metric = "' . $metric . '" WHERE metric = "' . $row['metric'] . '" AND day = "' . $row['day'] . '"');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
