<?php
    namespace Papi\Directus;
    use Directus\SDK\ClientRemote;
    use Papi\Request as R;

    class Request extends R {
        protected $_client;
        protected $_method;
        protected $_params;

        /**
         * @param cURL $curl
         */
        public function __construct(ClientRemote $client) {
            $this->_client = $client;
        }

        /**
         * What to do upon send
         *
         * @param callable $func
         */
        public function setMethod(callable $func) {
            $this->_method = $func;
        }

        /**
         * What additional parameters to pass
         */
        public function setParams() {
            $this->_params = func_get_args();
        }

        /**
         * Creates a fingerprint
         *
         * @return string
         */
        public function fingerprint() : string {
            $url = $this->_client->getBaseUrl();
            $hash = md5(print_r([ $url, $this->_method, $this->_params ], true));
            return $hash.'_'.$url;
        }

        /**
         * Sends the request
         *
         * @return array
         */
        public function send() : array {
            $response = call_user_func_array($this->_method, array_merge([ $this->_client ], $this->_params));
            return $response->getData();
        }
    }