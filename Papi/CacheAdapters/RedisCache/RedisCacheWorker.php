<?php
    namespace Papi\CacheAdapters\RedisCache;
    use Redis;
    use Tomaj\Hermes\Driver\RedisSetDriver;
    use Tomaj\Hermes\Dispatcher;
    use Tomaj\Hermes\Handler\HandlerInterface;
    use Tomaj\Hermes\MessageInterface;
    use anlutro\cURL\Request;
    use Papi\CacheAdapters\CacheAdapter;
    use Papi\CacheAdapters\RedisCache\RedisCache;

    // set default values
    $host = '127.0.0.1';
    $port = 6379;
    // check argv for overrides
    foreach($argv as $index => $value) {
        $eq_index = strpos($value, '=');
        if($eq_index===false) {
            $eq_index = strlen($value) - 1;
        }
        $key = substr($value, 0, $eq_index + 1);
        // switch the key
        switch($key) {
            case '--host=':
                $host = substr($value, $eq_index + 1);
                break;
            case '--port=':
                $port = intval(substr($value, $eq_index + 1));
                break;
            case '-h':
                $host = $argv[$index+1];
                $index++;
                break;
            case '-p':
                $port = intval($argv[$index+1]);
                $index++;
                break;
        }
    }

    // handler class
    class RedisCacheWorkerUpdateHandler implements HandlerInterface {
        protected $_redis;
        public function __construct(Redis $redis) {
            $this->_redis = $redis;
        }

        public function handle(MessageInterface $message) {
            $payload = $message->getPayload();
            // decode payload
            $request = unserialize($payload['request']);
            // execute query
            $response = $request->send();
            $this->_redis->set($request->fingerprint(), json_encode([
                'updated' => time(),
                'data' => json_decode($response, true)
            ]));
            return true;
        }
    }

    // connect to redis and hermes dispatcher
    $redis = new Redis();
    $redis->connect($host, $port);
    $dispatcher = new Dispatcher(new RedisSetDriver($redis));
    // attach handlers
    $dispatcher->registerHandler(CacheAdapter::EMIT_UPDATE, new RedisCacheWorkerUpdateHandler($redis));
    // wait for messages
    $dispatcher->handle();