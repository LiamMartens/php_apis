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

        /**
         * Creates a command to create an item
         *
         * @param string $table
         * @param array $data
         * @return array
         */
        public function createItem(string $table, array $data) : array {
            $r = new Request($this->_directus);
            $r->setMethod('createItem', function($directus, $table, $data) {
                return $directus->createItem($table, $data);
            });
            $r->setParams($table, $data);
            return $r->send();
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
         * Fetches items from a table
         *
         * @param string $table
         * @param array $data
         * @return array
         */
        public function getItems(string $table, array $params = []) : array {
            $r = new Request($this->_directus);
            $r->setMethod('getItems', function($directus, $table, $params) {
                return $directus->getItems($table, $params);
            });
            $r->setParams($table, $params);
            return $this->sendCacheableRequest($r);
        }
    }