<?php


namespace ZM\Requests;


use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Frame;

/**
 * Class ZMWebSocket
 * @package ZM\Utils
 * @since 1.5
 */
class ZMWebSocket
{
    private $parse;
    private $client;

    public $is_available = false;

    private $close_func;
    private $message_func;

    public function __construct($url, $set = ['websocket_mask' => true], $header = []) {
        $this->parse = parse_url($url);
        if (!isset($this->parse["host"])) {
            ZMRequest::$last_error = ("ZMRequest: url must contains scheme such as \"ws(s)://\"");
            return;
        }
        if (!isset($this->parse["path"])) $this->parse["path"] = "/";
        $port = $this->parse["port"] ?? (($this->parse["scheme"] ?? "ws") == "wss" ? 443 : 80);
        $this->client = new Client($this->parse["host"], $port, ($this->parse["scheme"] ?? "ws") == "wss");
        $this->client->set($set);
        if ($header != []) $this->client->setHeaders($header);
        $this->is_available = true;
    }

    /**
     * @return bool
     */
    public function upgrade() {
        if (!$this->is_available) return false;
        $r = $this->client->upgrade($this->parse["path"] . (isset($this->parse["query"]) ? ("?" . $this->parse["query"]) : ""));
        if ($r) {
            go(function () {
                while (true) {
                    $result = $this->client->recv(60);
                    if ($result === false) {
                        if ($this->client->connected === false) {
                            go(function () {
                                call_user_func($this->close_func, $this->client);
                            });
                            break;
                        }
                    } elseif ($result instanceof Frame) {
                        go(function () use ($result) {
                            $this->is_available = false;
                            call_user_func($this->message_func, $result, $this->client);
                        });
                    }
                }
            });
            return true;
        }
        return false;
    }

    /**
     * @param callable $callable
     * @return ZMWebSocket
     */
    public function onMessage(callable $callable) {
        $this->message_func = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function onClose(callable $callable) {
        $this->close_func = $callable;
        return $this;
    }
}
