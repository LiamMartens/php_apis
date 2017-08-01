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
            $c = new Command($this->_url, Command::METHOD_GET, 'tables/'.urlencode($this->_name).'/rows');
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute($params);
        }

        /**
         * Get a single record from a table by ID
         *
         * @param int $id
         * @return array
         */
        public function item(int $id) : array {
            $c = new Command($this->_url, Command::METHOD_GET, 'tables/'.urlencode($this->_name).'/rows/'.$id);
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute();
        }

        /**
         * Creates a new item in this table
         *
         * @param array $data
         * @return array
         */
        public function create(array $data) : array {
            $c = new Command($this->_url, Command::METHOD_POST, 'tables/'.urlencode($this->_name).'/rows');
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute($data);
        }
    }