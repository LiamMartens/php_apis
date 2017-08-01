<?php
    namespace Papi\GraphCMS;
    use Papi\CacheAdapters\CacheAdapterAware;
    use Papi\CacheAdapters\CacheAdapter;

    class GraphCMS extends CacheAdapterAware {
        const SIMPLE_URL = 'https://api.graphcms.com/simple/v1/';

        /** @var string Contains the project id */
        protected $_project;
        /** @var string Contains the auth token */
        protected $_token;
        /** @var array Contains queries */
        protected $_queries;

        public function __construct(string $project, string $token) {
            $this->_project = $project;
            $this->_token = $token;
            $this->_queries = [];
        }

        /**
         * Creates a new query
         *
         * @param string $type Type of query (query, mutation)
         * @param string $method The name of the method allModels, ...
         * @param array $fields Optional fields array
         * @param array $variables Optional variables array
         * @param array $params Optional params array
         *
         * @return Query
         */
        public function query(string $type, string $method, array $fields = [], array $variables = [], array $params = []) : Query {
            $q = new Query($type, $method, $fields, $variables, $params);
            if(!empty($cache=$this->getCacheAdapter())) {
                $q->setCacheAdapter($cache);
            }
            $q->setProjectId($this->_project)->setToken($this->_token);
            $this->_queries[] = $q;
            return $q;
        }

        /**
         * Executes the first non executed query
         *
         * @return array
         */
        public function execute(array $values = []) : array {
            foreach($this->_queries as $q) {
                if(!$q->executed()) {
                    // no cache, synchronously execute
                    return $q->execute($values);
                }
            }
            return [];
        }
    }