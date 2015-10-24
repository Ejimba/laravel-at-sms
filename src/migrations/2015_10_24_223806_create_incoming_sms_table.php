<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomingSmsTable extends Migration {

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

        Schema::create($table_prefix.'incoming_sms', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('source');
            $table->text('text')->nullable();
            $table->dateTime('received_time')->nullable();
            $table->string('gateway_message_id')->nullable();
            $table->string('link_id')->nullable();
            $table->boolean('processed')->default(false);
            $table->boolean('read')->default(false);
            $table->timestamps();
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
        Schema::drop($table_prefix.'incoming_sms');
    }
}