<?php

use Phinx\Migration\AbstractMigration;

class Statistic extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if( !$this->hasTable( 'Statistics' ) )
        {
            $table = $this->table( 'Statistics', [ 'id' => false, 'primary_key' => [ 'metric', 'day' ] ] );
            $table->addColumn( 'metric', 'string', [ 'length' => 100 ] )
                  ->addColumn( 'day', 'string', [ 'length' => 10 ] )
                  ->addColumn( 'val', 'text' )
                  ->addColumn( 'ts', 'integer');
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