<?php

namespace ProSales\WebSms\Facades;

use Illuminate\Support\Facades\Facade;
use ProSales\WebSms\Contracts\Message;
use ProSales\WebSms\Messages\SmsMessage;
use ProSales\WebSms\WebSmsClient;

/**
 * Class WebSms
 *
 * Provides facade access to the web sms client
 *
 * @method SmsMessage   smsMessage()            Create a new SMS message
 * @method void         send(Message $message)  Send a given message
 *
 * @author Aaron Schmied <aaron@pro-sales.ch>
 * @package ProSales\WebSms\Facades
 */
class WebSms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return WebSmsClient::class;
    }
}