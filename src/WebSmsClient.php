<?php

namespace ProSales\WebSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ProSales\WebSms\Contracts\Message;
use ProSales\WebSms\Exceptions\CommunicationException;
use ProSales\WebSms\Exceptions\ErrorException;
use ProSales\WebSms\Exceptions\InvalidRequestException;
use ProSales\WebSms\Exceptions\InvalidStatusException;
use ProSales\WebSms\Exceptions\NotAuthorizedException;
use ProSales\WebSms\Exceptions\NotSupportedException;
use ProSales\WebSms\Messages\SmsMessage;

class WebSmsClient
{
    const STATUS_OK = 2000;                             // Request accepted, Message(s) sent.
    const STATUS_OK_QUEUED = 2001;                      // Request accepted, Message(s) queued.
    const STATUS_INVALID_CREDENTIALS = 4001;            // Invalid Credentials. Inactive account or customer.
    const STATUS_INVALID_RECIPIENT = 4002;              // One or more recipients are not in the correct format or are containing invalid MSISDNs.
    const STATUS_INVALID_SENDER = 4003;                 // Invalid Sender. Sender address or type is invalid.
    const STATUS_INVALID_MESSAGE_TYPE = 4004;           // Invalid messageType.
    const STATUS_INVALIDMESSAGEID = 4008;               // Invalid clientMessageId.
    const STATUS_INVALID_TEXT = 4009;                   // Message text (messageContent) is invalid.
    const STATUS_MSG_LIMIT_EXCEEDED = 4013;             // Message limit is reached.
    const STATUS_UNAUTHORIZED_IP = 4014;                // Sender IP address is not authorized.
    const STATUS_INVALID_MESSAGE_PRIORITY = 4015;       // Invalid Message Priority.
    const STATUS_INVALID_COD_RETURNADDRES = 4016;       // Invalid notificationCallbackUrl.
    const STATUS_PARAMETER_MISSING = 4019;              // A required parameter was not given. The parameter name is shown in the statusMessage.
    const STATUS_INVALID_ACCOUNT = 4021;                // Account is invalid.
    const STATUS_ACCESS_DENIED = 4022;                  // Access to the API was denied.
    const STATUS_THROTTLING_SPAMMING_IP = 4023;         // Request limit exceeded for this IP address.
    const STATUS_THROTTLING_TOO_MANY_RECIPIENTS = 4025; // Transfer rate for immediate transmissions exceeded. Too many recipients in this request (1000).
    const STATUS_MAX_SMS_PER_MESSAGE_EXCEEDED = 4026;   // The message content results in too many (automatically generated) sms segments.
    const STATUS_INVALID_MESSAGE_SEGMENT = 4027;        // A messageContent segment is invalid
    const STATUS_RECIPIENTS_BLACKLISTED = 4031;         // All recipients blacklisted.
    const STATUS_INVALID_ATTACHMENT = 4034;             // Invalid attachment.
    const STATUS_INVALID_CONTENT_CATEGORY = 4040;       // Invalid contentCategory.
    const STATUS_INTERNAL_ERROR = 5000;                 // Internal error.
    const STATUS_SERVICE_UNAVAILABLE = 5003;            // Service unavailable.

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * The endpoint to make the api calls to
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The username to authenticate against the api
     *
     * @var string|null
     */
    protected $username = null;

    /**
     * The password for the given user
     *
     * @var string|null
     */
    protected $password = null;

    /**
     * The access token used to authenticate against the api
     *
     * @var string|null
     */
    protected $accessToken = null;
    
    /**
     * The access token used to authenticate against the api
     *
     * @var array
     */
    protected $guzzleOptions = [];

    /**
     * Build the content for the authorization header
     *
     * @see https://developer.websms.com/web-api/#authentication
     *
     * @return string
     */
    protected function getAuthorizationHeader()
    {
        if ($this->accessToken) {
            return "Bearer {$this->accessToken}";
        }

        $authToken = base64_encode(
            $this->username
            . ":" .
            $this->password
        );

        return "Basic {$authToken}";
    }

    /**
     * Set the api endpoint
     *
     * @param $endpoint string
     *
     * @return void
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        $this->createGuzzleClient();
    }

    /**
     * Return the api endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set additional guzzle options
     *
     * @param $options array
     *
     * @return void
     */
    public function setGuzzleOptions($options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * Set the access token for authentication
     *
     * @param $accessToken string
     *
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->createGuzzleClient();
    }

    /**
     * Set the username and password for authentication
     *
     * @param $username string
     * @param $password string
     *
     * @return void
     */
    public function setUsernamePassword($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->createGuzzleClient();
    }

    private function createGuzzleClient()
    {
        $this->guzzle = new Client($this->guzzleOptions + [
            'base_uri' => $this->endpoint,
            'headers' => [
                'Authorization' =>  $this->getAuthorizationHeader(),
                'Accept' =>         'application/json',
                'Content-Type' =>   'application/json',
            ],
        ]);
    }

    /**
     * Send a given message
     *
     * @param Message $message
     *
     * @return bool
     *
     * @throws CommunicationException
     * @throws ErrorException
     * @throws InvalidRequestException
     * @throws InvalidStatusException
     * @throws NotAuthorizedException
     */
    public function send(Message $message)
    {
        try {
            $response = $this
                ->guzzle
                ->request(
                    'POST',
                    $message->getRequestEndpoint(),
                    [
                        'body' => json_encode($message->getRequestData())
                    ]
                );

            if ($response->getStatusCode() !== 200) {
                throw new CommunicationException('Recieved an invalid status code.', $response->getStatusCode());
            }

            $responseBody = $response
                ->getBody()
                ->getContents();

            $responseData = json_decode($responseBody, true);

            switch (array_get($responseData, 'statusCode', 0)) {

                case self::STATUS_OK:
                case self::STATUS_OK_QUEUED:

                    return array_get($responseData, 'clientMessageId', null);

                case self::STATUS_INVALID_ACCOUNT:
                case self::STATUS_ACCESS_DENIED:
                case self::STATUS_INVALID_CREDENTIALS:
                case self::STATUS_UNAUTHORIZED_IP:

                    throw new NotAuthorizedException(
                        array_get($responseData, 'statusMessage', 'no message'),
                        array_get($responseData, 'statusCode', 0)
                    );

                case self::STATUS_INVALID_RECIPIENT:
                case self::STATUS_INVALID_SENDER:
                case self::STATUS_INVALID_MESSAGE_TYPE:
                case self::STATUS_INVALIDMESSAGEID:
                case self::STATUS_INVALID_TEXT:
                case self::STATUS_MSG_LIMIT_EXCEEDED:
                case self::STATUS_INVALID_MESSAGE_PRIORITY:
                case self::STATUS_INVALID_COD_RETURNADDRES:
                case self::STATUS_PARAMETER_MISSING:
                case self::STATUS_THROTTLING_SPAMMING_IP:
                case self::STATUS_THROTTLING_TOO_MANY_RECIPIENTS:
                case self::STATUS_MAX_SMS_PER_MESSAGE_EXCEEDED:
                case self::STATUS_INVALID_MESSAGE_SEGMENT:
                case self::STATUS_RECIPIENTS_BLACKLISTED:
                case self::STATUS_INVALID_ATTACHMENT:
                case self::STATUS_INVALID_CONTENT_CATEGORY:

                    throw new InvalidRequestException(
                        'There seems to be a problem with the request: '.
                        array_get($responseData, 'statusMessage', 'no message'),
                        array_get($responseData, 'statusCode', 0)
                    );

                case self::STATUS_INTERNAL_ERROR:
                case self::STATUS_SERVICE_UNAVAILABLE:

                    throw new ErrorException(
                        'The websms service seems to be unavailable at the moment.',
                        array_get($responseData, 'statusCode', 0)
                    );

                default:

                    throw new InvalidStatusException('The recieved status code is not known.');
            }

        }

        catch (GuzzleException $ex) {
            throw new CommunicationException($ex->getMessage(), $ex->getCode());
        }

        return false;
    }

    /**
     * Create a new sms message
     *
     * @return SmsMessage
     */
    public function smsMessage()
    {
        return new SmsMessage($this);
    }

    /**
     * Create a new whatsapp message
     *
     * @throws NotSupportedException
     */
    public function whatsAppMessage()
    {
        throw new NotSupportedException('WhatsApp messages are not implemented yet.');
    }
}
