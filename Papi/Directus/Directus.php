<?php
    namespace Papi\Directus;
    use Papi\CacheAdapters\CacheAdapterAware;

    class Directus extends CacheAdapterAware {
        protected $_base;
        protected $_key;
        protected $_api;
        public function __construct(string $url, string $key, string $api = '1.1') {
            $this->_base = $url;
            $this->_key = $key;
            $this->_api = $api;
        }
    }