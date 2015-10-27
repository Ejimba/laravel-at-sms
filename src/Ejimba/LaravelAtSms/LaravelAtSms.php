<?php

namespace Ejimba\LaravelAtSms;

use AfricasTalkingGateway\AfricasTalkingGateway;
use Ejimba\LaravelAtSms\LaravelAtSmsException;
use Carbon\Carbon;
use Config;

class LaravelAtSms {
    
    protected $username;
    protected $api_key;

    protected $incoming_sms;
    protected $outgoing_sms;

    protected $saved_incoming_sms;
    protected $saved_outgoing_sms;
    
    protected $incoming_sms_callbacks = array();
    protected $outgoing_sms_callbacks = array();
    
    protected $errors = array();
    protected $allowed_options = array('from', 'save', 'send_time');

    public function __construct()
    {
        $this->setUsername(Config::get('laravel-at-sms::username'));
        $this->setApiKey(Config::get('laravel-at-sms::api_key'));
        
        $incoming_model = Config::get('laravel-at-sms::incoming_sms.model', 'Ejimba\LaravelAtSms\Models\IncomingSms');
        $outgoing_model = Config::get('laravel-at-sms::outgoing_sms.model', 'Ejimba\LaravelAtSms\Models\OutgoingSms');
        
        $incoming_sms_callback = Config::get('laravel-at-sms::incoming_sms.callback');
        $outgoing_sms_callback = Config::get('laravel-at-sms::outgoing_sms.callback');

        if(is_array($incoming_sms_callback))
        {
            foreach ($incoming_sms_callback as $key => $callback)
            {
                $this->setIncomingSmsCallbacks($callback);
            }
        }
        elseif(!$this->isEmptyString($incoming_sms_callback))
        {
            $this->setIncomingSmsCallbacks($callback);
        }

        if(is_array($outgoing_sms_callback))
        {
            foreach ($outgoing_sms_callback as $key => $callback)
            {
                $this->setOutgoingSmsCallbacks($callback);
            }
        }
        elseif(!$this->isEmptyString($outgoing_sms_callback))
        {
            $this->setOutgoingSmsCallbacks($callback);
        }

        $this->incoming_sms = new $incoming_model;
        $this->outgoing_sms = new $outgoing_model;

    }

    public function sendMessage($to, $message, $options = array())
    {
        if($this->isEmptyString($to))
        {
            throw new LaravelAtSmsException("Missing the recepient of the message");
        }

        if($this->isEmptyString($message))
        {
            throw new LaravelAtSmsException("Missing message to send");
        }

        $recipients = '';

        if(is_array($to))
        {
            foreach ($to as $key => $r)
            {
                $recipients = $recipients.',';
            }
            $recipients = trim($recipients, ",");
        }
        else
        {
            $recipients = $to;
        }

        $destinations = explode(',', $recipients);

        // package default options
        $save = 0; $send_time = null;

        // gateway options
        $from = null;

        if(count($options))
        {
            $allowed_options = $this->getAllowedOptions();

            foreach ($options as $key => $option)
            {
                if(in_array($key, $allowed_options))
                {
                    switch ($key) {
                        case 'save':
                            $save = $option;
                            break;
                        case 'from':
                            $from = $option;
                            break;
                        case 'send_time':
                            $send_time = $option;
                            break;
                        case 'callbacks':
                            $this->clearOutgoingSmsCallbacks();
                            if(is_array($option))
                            {
                                foreach ($option as $key => $callback)
                                {
                                    $this->setOutgoingSmsCallbacks($callback);
                                }
                            }
                            else
                            {
                                $this->setOutgoingSmsCallbacks($option);
                            }
                            break;
                    }
                }
            }

        }

        foreach ($destinations as $key => $destination)
        {
            // we check if phone number formats are ok
        
            if(!$this->isValidPhoneNumber($destination))
            {
                throw new LaravelAtSmsException("Bad Phone Number Format for Message Recipient: ".$destination.". Should start with +country_prefix e.g. +254712345678");
            }

            if($send_time)
            {
                // this is a scheduled message

                $msg = array(
                        'destination' => $destination,
                        'text' => $message,
                        'send_time' => $send_time,
                    );

                // to add error catching here
                $this->saveOutgoingMessage($msg);

            }
            else
            {
                $gateway = new AfricasTalkingGateway($this->getUsername(), $this->getApiKey());

                try
                {
                    
                    $results = $gateway->sendMessage($destination, $message);
                    
                    foreach ($results as $key => $result)
                    {
                        if($result->status == 'Success')
                        {
                            if($save)
                            {
                                $msg = array(
                                        'destination' => $destination,
                                        'text' => $message,
                                        'processed' => 1,
                                        'sent' => 1,
                                        'sent_time' => Carbon::now(),
                                        'gateway_message_id' => $result->messageId,
                                        'cost' => $result->cost
                                    );

                                $this->saveOutgoingMessage($msg);
                            }

                            $callbacks = $this->getOutgoingSmsCallbacks();

                            if(isset($this->saved_outgoing_sms))
                            {
                                foreach ($callbacks as $key => $callback)
                                {
                                    // we separate the Controller and Method
                                    // saved as Controller@method

                                    $ctl = explode('@', $callback);

                                    if(count($ctl) == 2)
                                    {
                                        // Callback has the right format
                                        $controller = $ctl[0];
                                        $method = $ctl[1];

                                        $obj = new $controller;
                                        $obj->$method($this->saved_outgoing_sms);
                                    }
                                }
                            }
                            
                        }
                        else
                        {
                            // the gateway experienced an error


                        }
                    }
                }
                catch (Exception $e)
                {
                    throw new LaravelAtSmsException("Encountered an error while sending: ".$e->getMessage());
                }
            }
        }
    }

    public function saveAndSendMessage($to, $message, $options = array())
    {
        $options['save'] = 1;
        return $this->sendMessage($to, $message, $options);
    }

    public function getApiKey()
    {
        $api_key = $this->api_key;

        if($this->isEmptyString($api_key))
        {
            throw new LaravelAtSmsException("Missing API Key");
        }

        return $api_key;
    }

    public function getUsername()
    {
        $username = $this->username;

        if($this->isEmptyString($username))
        {
            throw new LaravelAtSmsException("Missing API Username");
        }

        return $username;
    }

    public function getAllowedOptions()
    {
        return $this->allowed_options;
    }

    public function getIncomingSmsCallbacks()
    {
        return $this->incoming_sms_callbacks;
    }

    public function getOutgoingSmsCallbacks()
    {
        return $this->outgoing_sms_callbacks;
    }

    public function getErrors($key = null)
    {
        if(is_null($key))
        {
            return $this->errors;
        }
        else
        {
            $all_errors = $this->errors;

            if(array_key_exists($key, $all_errors))
            {
                return $all_errors[$key];
            }
            else
            {
                return array();
            }
            
        }
    }

    public function setApiKey($key)
    {
        $this->api_key = $key;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setErrors($error, $key = null)
    {
        if($key)
        {
            $this->errors[$key] = $error;
        }
        else
        {
            $this->errors[] = $error;
        }
    }

    public function clearIncomingSmsCallbacks()
    {
        $this->incoming_sms_callbacks = array();
    }

    public function clearOutgoingSmsCallbacks()
    {
        $this->outgoing_sms_callbacks = array();
    }

    public function setIncomingSmsCallbacks($callback)
    {
        $this->incoming_sms_callbacks[] = $callback;
    }

    public function setOutgoingSmsCallbacks($callback)
    {
        return $this->outgoing_sms_callbacks[] = $callback;
    }

    public function saveIncomingMessage($message_data)
    {
        $incoming_sms = $this->incoming_sms;

        foreach ($message_data as $key => $data)
        {
            $incoming_sms->$key = $data;
        }

        $res = $incoming_sms->save();

        if($res)
        {
            $this->saved_incoming_sms = $incoming_sms;
        }

        return $res;
    }

    public function saveOutgoingMessage($message_data)
    {
        $outgoing_sms = $this->outgoing_sms;

        foreach ($message_data as $key => $data)
        {
            $outgoing_sms->$key = $data;
        }
        
        $res = $outgoing_sms->save();

        if($res)
        {
            $this->saved_outgoing_sms = $outgoing_sms;
        }

        return $res;
    }

    public function isValidPhoneNumber($phone_number)
    {
        if (strpos($phone_number, '+') !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function isEmptyString($str)
    {
        if (strlen(trim($str)) > 0)
        {
            return false;
        }
        else
        {
            return true;
        }

    }
    
}