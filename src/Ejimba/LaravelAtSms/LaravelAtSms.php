<?php

namespace Ejimba\LaravelAtSms;

use AfricasTalkingGateway\AfricasTalkingGateway;
use Ejimba\LaravelAtSms\LaravelAtSmsException;
use Carbon\Carbon;
use Config;

class LaravelAtSms {
    
    protected $username;
    protected $apiKey;
    protected $incoming_sms;
    protected $outgoing_sms;
    protected $incoming_sms_model;
    protected $incoming_sms_callback;
    protected $outgoing_sms_model;
    protected $outgoing_sms_callback;

    public function __construct()
    {
        $this->username = Config::get('laravel-at-sms::username');
        $this->apiKey = Config::get('laravel-at-sms::api_key');
        $this->incoming_sms_model = Config::get('laravel-at-sms::incoming_sms.model', 'Ejimba\LaravelAtSms\Models\IncomingSms');
        $this->outgoing_sms_model = Config::get('laravel-at-sms::outgoing_sms.model', 'Ejimba\LaravelAtSms\Models\OutgoingSms');
        $this->incoming_sms_callback = Config::get('laravel-at-sms::incoming_sms.callback');
        $this->outgoing_sms_callback = Config::get('laravel-at-sms::outgoing_sms.callback');
        $this->incoming_sms = new $this->incoming_sms_model;
        $this->outgoing_sms = new $this->outgoing_sms_model;

        if($this->username == ''){
            throw new LaravelAtSmsException("Missing API Username", 1);
        }

        if($this->apiKey == ''){
            throw new LaravelAtSmsException("Missing API Key", 1);
        }

    }

    public function sendMessage($to, $message, $from = null, $options = array())
    {
        if(is_null($to)){
            throw new LaravelAtSmsException("Missing the recepient of the message", 1);
        }

        if(is_null($message)){
            throw new LaravelAtSmsException("Missing message to send", 1);
        }

        $recipients = '';

        if(is_array($to))
        {
            foreach ($to as $key => $r) {
                $recipients = $recipients.',';
            }

            $recipients = trim($recipients, ",");
        }
        else
        {
            $recipients = $to;
        }

        $dest = explode(',', $recipients);
        
        // we check if phone number formats are ok
        foreach ($dest as $key => $des)
        {
            if (strpos($des, '+') === FALSE)
            {
                throw new LaravelAtSmsException("Bad Phone Number Format for Message Recipient: ".$des.". Should start with +country_prefix e.g. +254712345678", 1);                
            }
        }

        foreach ($dest as $key => $des)
        {
            $outgoing_sms = $this->outgoing_sms;
            $outgoing_sms->destination = $des;
            $outgoing_sms->text = $message;
            $outgoing_sms->processed = 1;
            $outgoing_sms->save();
        }

        $gateway = new AfricasTalkingGateway($this->username, $this->apiKey);

        try
        {
            $results = $gateway->sendMessage($recipients, $message);
            
            foreach ($results as $key => $result) {
                
                $outgoing_sms = $this->outgoing_sms->where('destination', '=', $result->number)
                                                    ->where('processed', '=', 1)->where('sent', '=', 0)
                                                    ->first();
                
                if(!is_null($outgoing_sms))
                {
                    if($result->status == 'Success')
                    {
                        $outgoing_sms->sent = 1;
                        $outgoing_sms->sent_time = Carbon::now();
                        $outgoing_sms->gateway_message_id = $result->messageId;
                        $outgoing_sms->cost = $result->cost;
                        $outgoing_sms->save();

                        if(!$this->outgoing_sms_callback == '')
                        {
                            $outgoing_sms_callback = $this->outgoing_sms_callback;
                            $ctl = explode('@', $outgoing_sms_callback);
                            if(count($ctl) == 2)
                            {
                                // Config has the right format
                                $controller = $ctl[0];
                                $method = $ctl[1];

                                $obj = new $controller;
                                $obj->$method($outgoing_sms);
                            }
                        }
                    }
                    else
                    {
                        $outgoing_sms->retries = $outgoing_sms->retries + 1;
                        $outgoing_sms->last_retry_time = Carbon::now();
                        $outgoing_sms->save();
                    }
                }
            }

            return $results;
        }
        catch (Exception $e)
        {
            throw new LaravelAtSmsException("Encountered an error while sending: ".$e->getMessage(), 1);
        }
    }
    
}