<?php
/**
 * Created by PhpStorm.
 * User: aaronschmied
 * Date: 22.08.18
 * Time: 15:30
 */

namespace ProSales\WebSms\Messages;


use ProSales\WebSms\WebSmsClient;

abstract class Message
{
    const CONTENT_CATEGORY_INFORMATIONAL = 'informational';
    const CONTENT_CATEGORY_ADVERTISEMENT = 'advertisement';

    /**
     * @var WebSmsClient
     */
    protected $client;

    /**
     * List of recipients (E164 formatted) to whom the message should
     * be sent. The list of recipients may contain a maximum of 1000 entries.
     *
     * @see en.wikipedia.org/wiki/MSISDN
     *
     * @var array
     */
    public $recipientAddressList = [];

    /**
     * The encoded message content
     *
     * @var string
     */
    public $messageContent = '';

    /**
     * If set to true, the transmission is only simulated, no SMS is sent.
     * Depending on the number of recipients the status code 2000 or
     * 2001 is returned.
     *
     * @var bool
     */
    public $test = false;

    /**
     * May contain a freely definable message id.
     *
     * @var string
     */
    public $clientMessageId = '';

    /**
     * The content category that is used to categorize the message
     * (used for blacklisting).
     *
     * @var string
     */
    public $contentCategory = self::CONTENT_CATEGORY_INFORMATIONAL;

    /**
     * Specifies the validity periode (in seconds) in which the message
     * is tried to be delivered to the recipient.
     *
     * @var int
     */
    public $validityPeriode = 300;

    /**
     * Message constructor.
     *
     * @param WebSmsClient $client
     */
    public function __construct(WebSmsClient $client)
    {
        $this->client = $client;
    }

    /**
     * Add a message recipient
     *
     * @param string $recipient
     *
     * @return $this
     */
    public function to(string $recipient)
    {
        $this->recipientAddressList[] = $recipient;
        return $this;
    }

    /**
     * Set the text content
     *
     * @param string $content
     *
     * @return $this
     */
    public function text(string $content)
    {
        $this->messageContent = $content;
        return $this;
    }

    /**
     * Send the message
     */
    public function send()
    {
        return $this
            ->client
            ->send($this);
    }

    /**
     * Enable fake message sending
     *
     * @param bool $fake
     *
     * @return $this
     */
    public function fake($fake = true)
    {
        $this->test = $fake;
        return $this;
    }
}