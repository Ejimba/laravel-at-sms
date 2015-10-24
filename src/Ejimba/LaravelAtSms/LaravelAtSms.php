<?php

namespace Ejimba\LaravelAtSms;

use AfricasTalkingGateway\AfricasTalkingGateway;
use Ejimba\LaravelAtSms\Models\IncomingSms;
use Ejimba\LaravelAtSms\Models\OutgoingSms;

class LaravelAtSms {
    
    protected $username;
    protected $apiKey;

    public function __construct()
    {
        $this->username = Config::get('laravel-at-sms::username');
        $this->apiKey = Config::get('laravel-at-sms::api_key');
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
            return $results;
        }
        catch (Exception $e)
        {
            throw new LaravelAtSmsException("Encountered an error while sending: ".$e->getMessage(), 1);
        }
    }

    public function getOutgoingSms()
    {
        $outgoing_sms = OutgoingSms::all();
        return $outgoing_sms;
    }

    public function getIcomingSms()
    {
        $incoming_sms = IncomingSms::all();
        return $incoming_sms;
    }
}