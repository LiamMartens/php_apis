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

        /**
         * Gets the base url for directus
         *
         * @return string
         */
        public function getBaseUrl() : string {
            return $this->_base;
        }

        /**
         * Gets the API url for directus
         *
         * @return string
         */
        public function getApiUrl() : string {
            return trim($this->_base, '/').'/api/'.$this->_api;
        }

        /**
         * Creates a new command
         *
         * @param string $method
         * @param string $path
         * @param array $data
         * @return Command
         */
        public function command(string $method, string $path, array $data = []) : Command {
            $command = new Command($this->getApiUrl(), $method, $path, $data);
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $command;
        }

        /**
         * Creates a table commands wrapper
         *
         * @param string $name
         * @return Table
         */
        public function table(string $name) : Table {
            $table = new Table($this->getApiUrl(), $name);
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $table;
        }
    }