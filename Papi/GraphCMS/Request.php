<?php
    namespace Papi\GraphCMS;
    use anlutro\cURL\cURL;
    use anlutro\cURL\Request as CurlRequest;
    use Papi\Request as R;

    class Request extends R {
        protected $_request;

        public function setRequest(CurlRequest $r) {
            $this->_request = $r;
        }

        public function fingerprint() : string {
            // get fingerprint variables
            $method = $this->_request->getMethod();
            $url = $this->_request->getUrl();
            $headers = $this->_request->getHeaders();
            $cookies = $this->_request->getCookies();
            $data = $this->_request->getData();
            $encoding = $this->_request->getEncoding();
            $options = $this->_request->getOptions();
            $params = [ $method, $url, $headers, $cookies, $data, $encoding, $options ];
            // create hash fingerprint
            $hash = md5(print_r($params, true));
            return $hash.'_'.$r->getUrl();
        }

        public function send() {
            $response = $this->_request->send();
            if($response->statusCode > 299) {
                return [];
            }
            return $response->body;
        }
    }