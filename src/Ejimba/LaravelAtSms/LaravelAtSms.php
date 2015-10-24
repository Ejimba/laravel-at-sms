<?php

namespace Ejimba\LaravelAtSms;

use AfricasTalkingGateway\AfricasTalkingGateway;
use Ejimba\LaravelAtSms\Models\IncomingSms;
use Ejimba\LaravelAtSms\Models\OutgoingSms;
use Ejimba\LaravelAtSms\LaravelAtSmsException;
use Carbon\Carbon;
use Config;

class LaravelAtSms {
    
    protected $username;
    protected $apiKey;

    public function __construct()
    {
        $this->username = Config::get('laravel-at-sms::username');
        $this->apiKey = Config::get('laravel-at-sms::api_key');

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
        
        foreach ($dest as $key => $des) {
            $outgoing_sms = new OutgoingSms;
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
                
                $outgoing_sms = OutgoingSms::where('destination', '=', $result->number)->where('processed', '=', 1)->where('sent', '=', 0)->first();
                
                if(!is_null($outgoing_sms))
                {
                    if($result->status == 'Success')
                    {
                        $outgoing_sms->sent = 1;
                        $outgoing_sms->sent_time = Carbon::now();
                        $outgoing_sms->gateway_message_id = $result->messageId;
                        $outgoing_sms->cost = $result->cost;
                        $outgoing_sms->save();
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

    public function getIcomingSms()
    {
        $incoming_sms = IncomingSms::all();
        return $incoming_sms;
    }

    public function getOutgoingSms()
    {
        $outgoing_sms = OutgoingSms::all();
        return $outgoing_sms;
    }
    
}