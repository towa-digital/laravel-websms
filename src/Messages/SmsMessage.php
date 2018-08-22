<?php

namespace ProSales\WebSms\Messages;

use ProSales\WebSms\Messages\Message as BaseMessage;
use ProSales\WebSms\Contracts\Message as MessageContract;

class SmsMessage extends BaseMessage implements MessageContract
{
    const SENDER_ADDRESS_TYPE_NATIONAL = 'national';
    const SENDER_ADDRESS_TYPE_INTERNATIONAL = 'international';
    const SENDER_ADDRESS_TYPE_ALPHANUMERIC = 'alphanumeric';
    const SENDER_ADDRESS_TYPE_SHORTCODE = 'shortcode';

    /**
     * The sender address type.
     *
     * @var string
     */
    public $senderAddressType = self::SENDER_ADDRESS_TYPE_INTERNATIONAL;

    /**
     * Address of the sender (assigned to the account) from
     * which the message is sent.
     *
     * @var string
     */
    public $senderAddress = null;

    /**
     * The message is sent as flash SMS (displayed directly
     * on the screen of the mobile phone).
     *
     * @var bool
     */
    public $sendAsFlashSms = false;

    /**
     * Set the from address
     *
     * @param string $address
     * @param string $type
     *
     * @return $this
     */
    public function from($address, $type = 'international')
    {
        $this->senderAddress = $address;
        $this->senderAddressType = $type;
        return $this;
    }

    /**
     * If the message should be sent as a flash message.
     * (not recommended)
     *
     * @param bool $asFlash
     *
     * @return $this
     */
    public function asFlash($asFlash = true)
    {
        $this->sendAsFlashSms = $asFlash;
        return $this;
    }

    /**
     * Return the request endpoint
     *
     * @return string
     */
    public function getRequestEndpoint()
    {
        return 'smsmessaging/text';
    }

    /**
     * Return the request data
     *
     * @return array
     */
    public function getRequestData()
    {
        return [
            'senderAddressType' =>      $this->senderAddressType,
            'senderAddress' =>          $this->senderAddress,

            'recipientAddressList' =>   $this->recipientAddressList,

            'messageContent' =>         $this->messageContent,
            'contentCategory' =>        $this->contentCategory,

            'validityPeriode' =>        $this->validityPeriode,

            'sendAsFlashSms' =>         $this->sendAsFlashSms,

            'clientMessageId' =>        $this->clientMessageId,

            'test' =>                   $this->test,
        ];
    }
}