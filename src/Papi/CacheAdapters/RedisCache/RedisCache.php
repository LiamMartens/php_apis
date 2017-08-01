<?php
    namespace Papi\CacheAdapters\RedisCache;
    use Papi\CacheAdapters\CacheAdapter;
    /**
     * Requires PHP to be compiled with Redis support
     * or include third party Redis
     */
    use Redis;
    /**
     * Requires tomaj/hermes and curl package for
     * composer
     */
    use Tomaj\Hermes\Message;
    use Tomaj\Hermes\Emitter;
    use Tomaj\Hermes\Driver\RedisSetDriver;
    use Papi\Request;

    class RedisCache extends CacheAdapter {
        /** @var string The Redis host */
        protected $_host;
        /** @var int The Redis port */
        protected $_port;
        /** @var int The time for expiration */
        protected $_expiration;
        /** @var Redis The redis connection */
        protected $_redis;
        /** @var Emitter The hermes message emitter */
        protected $_emitter;

        public function __construct(string $host = '127.0.0.1', int $port = 6379, int $expiration = 3600) {
            // set variables
            $this->_host = $host;
            $this->_port = $port;
            $this->_expiration = $expiration;
            // connect to redis
            $this->_redis = new Redis();
            $this->_redis->connect($this->_host, $this->_port);
            // create emitter
            $this->_emitter = new Emitter(new RedisSetDriver($this->_redis));
        }


        /**
         * For getting a value from cache
         *
         * @param Request $request
         * @param array $default
         * @return array
         */
        public function get(Request $request, bool &$expired = null) : array {
            // create fingerprint
            $fingerprint = $request->fingerprint();
            // get cached data
            $data = $this->_redis->get($fingerprint);
            // return empty if key didn't exist
            if($data===false) {
                return [];
            }
            // check expiration
            $data = json_decode($data, true);
            $diff = time() - $data['updated'];
            $expired = ($diff > $this->_expiration);
            // return the data
            return $data['data'];
        }

        /**
         * For directly setting a value in cache
         *
         * @param Request $request
         * @param array $data
         * @return bool
         */
        public function set(Request $request, array $data) : bool {
            return $this->_redis->set($request->fingerprint(), json_encode([
                'updated' => time(),
                'data' => $data
            ]));
        }

        /**
         * For updating a value in cache
         * this could be using an emitter with
         * beanstalk, this could be synchronously...
         *
         * @param Curl $request
         * @return bool
         */
        public function update(Request $request) : bool {
            $message = new Message(CacheAdapter::EMIT_UPDATE, [
                'request' => serialize($request)
            ]);
            return (!!$this->_emitter->emit($message));
        }
    }