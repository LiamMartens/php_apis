<?php
    namespace Papi\Directus;
    use Papi\CacheAdapters\CacheAdapterAware;
    use Directus\SDK\ClientFactory;
    use anlutro\cURL\cURL;

    class Directus extends CacheAdapterAware {
        protected $_base;
        protected $_token;
        protected $_api;
        protected $_curl;
        protected $_directus;

        public function __construct(string $url, string $token, string $api = '1.1') {
            $this->_base = $url;
            $this->_token = $token;
            $this->_api = $api;
            // create new directus
            $this->_directus = ClientFactory::create($this->_token, [
                'base_url' => $this->_base,
                'version' => $this->_api
            ]);
        }

        protected function sendCacheableRequest(Request $r) : array {
            $cache = $this->getCacheAdapter();
            // get from cache if available
            if(!empty($cache)) {
                $expired = false;
                $data = $cache->get($r, $expired);
                // if empty? return synchronously
                if(empty($data)) {
                   $data = $r->send();
                   $cache->set($r, $data);
                   return $data;
                }
                // if expired call for update and return cached data
                if($expired) {
                    $cache->update($r);
                }
                $this->_executed = true;
                return $data;
            }
            return $r->send();
        }

        /**
         * For passing methods through to directus
         *
         * @param string $name
         * @param array $arguments
         * @return mixed
         */
        public function __call(string $name, array $arguments) {
            if(!method_exists($this, $name)&&method_exists($this->_directus, $name)) {
                $r = new Request($this->_directus);
                $r->setMethod($name);
                $r->setParams($arguments);
                if(preg_match('/^create$/', $name)) {
                    return $r->send();
                }
                return $this->sendCacheableRequest($r);
            }
            return false;
        }
    }