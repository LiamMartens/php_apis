<?php
    namespace Papi\Directus;
    use Papi\CacheAdapters\CacheAdapterAware;

    class Table extends CacheAdapterAware {
        /** @var string The URL of the API endpoints */
        protected $_url;
        /** @var string The name of the table */
        protected $_name;

        public function __construct(string $apiurl, string $name) {
            $this->_url = $apiurl;
            $this->_name = $name;
        }

        /**
         * Gets the rows from a table
         *
         * @param array $params
         * @return array
         */
        public function rows(array $params = []) : array {
            $c = new Command($this->_url, Command::METHOD_GET, 'tables/'.$this->_name.'/rows', $params);
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute();
        }
    }