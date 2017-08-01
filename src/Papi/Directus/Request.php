<?php
    namespace Papi\Directus;
    use Directus\SDK\ClientRemote;
    use Directus\SDK\ClientFactory;
    use Papi\Request as R;

    class Request extends R {
        protected $_url;
        protected $_token;
        protected $_api;
        protected $_method;
        protected $_params;

        /**
         * @param cURL $curl
         */
        public function __construct(ClientRemote $client) {
            // ClientRemote is not serializeable
            // save base url and token
            $this->_url = $client->getBaseUrl();
            $this->_token = $client->getAccessToken();
            $this->_api = $client->getAPIVersion();
        }

        /**
         * What to do upon send
         *
         * @param string $name
         */
        public function setMethod(string $name) {
            $this->_method = $name;
        }

        /**
         * What additional parameters to pass
         *
         * @param array $params
         */
        public function setParams(array $params) {
            $this->_params = $params;
        }

        /**
         * Creates a fingerprint
         *
         * @return string
         */
        public function fingerprint() : string {
            $hash = md5(print_r([ $this->_url, $this->_method, $this->_params ], true));
            return $hash.'_'.$this->_url.'/'.$this->_method;
        }

        /**
         * Sends the request
         *
         * @return array
         */
        public function send() : array {
            $response = call_user_func_array([
                ClientFactory::create($this->_token, [
                    'base_url' => $this->_url,
                    'version' => $this->_api
                ])
            , $this->_method ], $this->_params);
            return $response->getRawData()['data'];
        }
    }