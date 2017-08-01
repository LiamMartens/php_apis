<?php
    namespace Papi\Directus;
    use Papi\CacheAdapters\CacheAdapterAware;

    class Files extends CacheAdapterAware {
        /** @var string The URL of the API endpoints */
        protected $_url;

        public function __construct(string $apiurl) {
            $this->_url = $apiurl;
        }

        /**
         * Gets all files
         *
         * @param array $params
         * @return array
         */
        public function all(array $params = []) : array {
            $c = new Command($this->_url, Command::METHOD_GET, 'files');
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute($params);
        }

        /**
         * Gets a single file from Directus by ID
         *
         * @param int $id
         * @return array
         */
        public function get(int $id) : array {
            $c = new Command($this->_url, Command::METHOD_GET, 'files/'.$id);
            if(!empty($cache=$this->getCacheAdapter())) {
                $c->setCacheAdapter($cache);
            }
            return $c->execute();
        }
    }