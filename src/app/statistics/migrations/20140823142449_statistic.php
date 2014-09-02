<?php

use Phinx\Migration\AbstractMigration;

class statistic extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if ( !$this->hasTable( 'Statistics' ) ) {
            $table = $this->table( 'Statistics', [ 'id' => false, 'primary_key' => [ 'metric', 'day' ] ] );
            $table->addColumn( 'metric', 'string', [ 'length' => 100 ] )
                  ->addColumn( 'day', 'string', [ 'length' => 10 ] )
                  ->addColumn( 'val', 'string' )
                  ->addColumn( 'ts', 'integer')
                  ->create();
        }
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
