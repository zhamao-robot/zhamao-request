<?php


namespace ZM\Requests;


use Swoole\Coroutine\Client;

class ZMClient
{
    /**
     * @var Client
     */
    private $client;

    private $on = [];

    public $is_available = false;

    private $recursive = 3;

    public function __construct($type, $set = []) {
        ;
        $this->client = new Client($type);
        $this->client->set($set);
        $this->is_available = true;
    }

    /**
     * @param $host
     * @param $port
     * @return bool
     */
    public function connect($host, $port) {
        --$this->recursive;
        if ($this->recursive < 0) return false;
        if (!$this->is_available) return false;
        $r = $this->client->connect($host, $port);
        if ($r !== false) {
            go(function (){
                call_user_func($this->on["connect"], $this->client);
            });
            go(function (){
                while (true) {
                    $result = $this->client->recv();
                    if ($result === false) {
                        if ($this->client->errCode !== 110) {
                            go(function() {
                                call_user_func($this->on["close"], $this->client);
                            });
                            $this->client->close();
                            $this->is_available = false;
                            break;
                        }
                    } elseif (strlen($result) == 0) {
                        go(function() {
                            call_user_func($this->on["close"], $this->client);
                        });
                        $this->client->close();
                        $this->is_available = false;
                        break;
                    } else {
                        go(function () use ($result) {
                            call_user_func($this->on["recv"], $result, $this->client);
                        });
                    }
                }
            });
            return true;
        } else {
            $this->client->close();
            $r = $this->connect($host, $port);
            if ($r === false) {
                return false;
            }
            return true;
        }
    }

    /**
     * @param callable $callable
     * @return ZMClient
     */
    public function onConnect(callable $callable) {
        $this->on["connect"] = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function onClose(callable $callable) {
        $this->on["close"] = $callable;
        return $this;
    }

    public function onRecv(callable $callable) {
        $this->on["recv"] = $callable;
        return $this;
    }

    public function isConnected() {
        return $this->is_available;
    }

    public function send($data) {
        return $this->client->send($data);
    }

    /**
     * @return Client
     */
    public function getClient(): Client {
        return $this->client;
    }
}