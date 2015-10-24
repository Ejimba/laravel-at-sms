<?php

namespace Ejimba\LaravelAtSms\Models;

use Config, Eloquent;

class OutgoingSms extends Eloquent{

    /**
     * Table prefix
     *
     * @var string
     */
    protected $table_prefix = '';

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct()
    {
        // Set the prefix
        $this->table_prefix = Config::get('laravel-at-sms::table_prefix', '');
        $this->setTable($this->table_prefix.'outgoing_sms');
    }
}