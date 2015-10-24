<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutgoingSmsTable extends Migration {

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

        Schema::create($table_prefix.'outgoing_sms', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('destination');
            $table->text('text');
            $table->integer('incoming_sms_id')->unsigned()->nullable();
            $table->dateTime('send_time')->nullable();
            $table->boolean('processed')->default(false);
            $table->boolean('sent')->default(false);
            $table->dateTime('sent_time')->nullable();
            $table->string('gateway_message_id')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('delivery_failure_reason')->nullable();
            $table->integer('retries')->unsigned()->default(0);
            $table->dateTime('last_retry_time')->nullable();
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
        Schema::drop($table_prefix.'outgoing_sms');
    }
}