<?php
    namespace Papi\CacheAdapters;
    use anlutro\cURL\Request;

    abstract class CacheAdapter {
        const EMIT_UPDATE = 'Papi/CacheAdapters/RedisCache/RedisCache/update';

        /**
         * Creates a string fingerprint from a Request
         *
         * @param Request $r
         * @return string
         */
        public static function createFingerprint(Request $r) : string {
            // get fingerprint variables
            $method = $r->getMethod();
            $url = $r->getUrl();
            $headers = $r->getHeaders();
            $cookies = $r->getCookies();
            $data = $r->getData();
            $encoding = $r->getEncoding();
            $options = $r->getOptions();
            $params = [ $method, $url, $headers, $cookies, $data, $encoding, $options ];
            // create hash fingerprint
            $hash = md5(print_r($params, true));
            return $hash.'_'.$r->getUrl();
        }

        /**
         * Creates a string fingerprint from a Request
         *
         * @param Request $r
         * @return string
         */
        public function fingerprint(Request $r) : string {
            return CacheAdapter::createFingerprint($r);
        }

        /**
         * For getting a value from cache
         *
         * @param Request $query
         * @param array $default
         * @return array
         */
        public abstract function get(Request $request, bool &$expired = null) : array;

        /**
         * For directly setting a value in cache
         *
         * @param Request $query
         * @param array $data
         * @return bool
         */
        public abstract function set(Request $request, array $data) : bool;

        /**
         * For updating a value in cache
         * this could be using an emitter with
         * beanstalk, this could be synchronously...
         *
         * @param Request $request
         * @return bool
         */
        public abstract function update(Request $request) : bool;
    }