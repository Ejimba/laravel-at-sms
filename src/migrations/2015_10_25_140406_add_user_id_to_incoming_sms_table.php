<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToIncomingSmsTable extends Migration {

    protected $table_prefix;

	public function __construct()
    {
        // Get the table_prefix
        $this->table_prefix = Config::get('laravel-at-sms::table_prefix', '');
    }

	/**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Bring to local scope
        $table_prefix = $this->table_prefix;

        Schema::table($table_prefix.'incoming_sms', function($table)
        {
            $table->integer('user_id')->unsigned()->nullable();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         // Bring to local scope
        $table_prefix = $this->table_prefix;

        // Drop the tables involved
        Schema::table($table_prefix.'incoming_sms', function($table)
        {
            $table->dropColumn('user_id');
        });
    }
}