<?php
    namespace Papi\Directus;
    use Papi\CacheAdapters\CacheAdapterAware;
    use anlutro\cURL\cURL;
    use anlutro\cURL\Request;

    class Command extends CacheAdapterAware {
        const METHOD_GET = 'GET';
        const METHOD_POST = 'POST';
        const METHOD_DELETE = 'DELETE';
        const METHOD_PUT = 'PUT';

        /** @var string The API url to request to */
        protected $_url;
        /** @var string The API token for access */
        protected $_key;
        /** @var string What kind of method to execute */
        protected $_method;
        /** @var string The API path, what to do */
        protected $_path;
        /** @var bool Whether the command has been executed */
        protected $_executed;

        public function __construct(string $apiurl, string $method, string $path) {
            $this->_url = $apiurl;
            $this->_method = $method;
            $this->_path = $path;
            $this->_executed = false;
        }

        /**
         * Sets the authorization token
         *
         * @param string $key
         * @return Command
         */
        public function setToken(string $key) : Command {
            $this->_key = $key;
            return $this;
        }

        /**
         * Whether the command was already executed
         *
         * @return bool
         */
        public function executed() : bool {
            return $this->_executed;
        }

        /**
         * Sets the command to executed
         * without executing
         *
         * @return Command
         */
        public function cached() : Command {
            $this->_executed = true;
            return $this;
        }

        /**
         * Builds a new request from the parameters and data
         *
         * @param array $values
         * @return Request
         */
        protected function request(array $values = []) : Request {
            $curl = new cURL();
            // build the url
            $url = $curl->buildUrl($this->_url.'/'.trim($this->_path, '/'),
                    ($this->_method==Command::METHOD_GET) ? $data : []);
            // build the request
            $request = $curl->newJsonRequest($this->_method, $url,
                        ($this->_method!=Command::METHOD_GET) ? $data : []);
            $request->setHeader('Authorization', 'Bearer '.$this->_key);
            // return the request
            return $request;
        }

        /**
         * Executes the command and returns
         * it's response
         *
         * @param $values
         * @return array
         */
        public function execute(array $values = []) : array {
            $cache = $this->getCacheAdapter();
            $request = $this->request($values);
            // get from cache if available
            if(!empty($cache)) {
                $expired = false;
                $data = $cache->get($request, $expired);
                // if empty? return synchronously
                if(empty($data)) {
                   $response = $request->send();
                   $data = json_decode($response->body, true);
                   $cache->set($request, $data);
                   return $data; 
                }
                // if expired call for update and return cached data
                if($expired) {
                    $cache->update($request);
                }
                $this->_executed = true;
                return $data;
            }
            // just execute sychronously
            $this->_executed = true;
            $response = $request->send();
            return json_decode($response->body, true);
        }
    }