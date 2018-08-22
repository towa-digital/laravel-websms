<?php

namespace ProSales\WebSms\Contracts;

interface Message
{
    /**
     * Get the request endpoint
     *
     * @return string
     */
    public function getRequestEndpoint();

    /**
     * Build the request data
     *
     * @return array
     */
    public function getRequestData();
}