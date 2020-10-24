<?php /** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */

function zm_request_get($url, $headers = [], $set = [], $return_body = true)
{
    return \ZM\Requests\ZMRequest::get($url, $headers, $set, $return_body);
}

function zm_request_post($url, array $header, $data, $set = [], $return_body = true)
{
    return \ZM\Requests\ZMRequest::post($url, $header, $data, $set, $return_body);
}

function zm_websocket($url, $set = ['websocket_mask' => true], $header = [])
{
    return new \ZM\Requests\ZMWebSocket($url, $set, $header);
}

function zm_request($url, $attribute = [], $return_body = true)
{
    return \ZM\Requests\ZMRequest::request($url, $attribute, $return_body);
}
