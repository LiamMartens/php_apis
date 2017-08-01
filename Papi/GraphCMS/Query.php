<?php
    namespace Papi\GraphCMS;
    use Papi\CacheAdapters\CacheAdapterAware;
    use Papi\CacheAdapters\CacheAdapter;
    use anlutro\cURL\cURL;
    use anlutro\cURL\Request as R;

    class Query extends CacheAdapterAware {
        /**
         * The 2 currently supported GraphQL
         * query types in GraphCMS
         */
        const TYPE_MUTATION = 'mutation';
        const TYPE_QUERY = 'query';

        /** @var string The GraphCMS project id */
        protected $_project = null;
        /** @var string The GraphCMS token */
        protected $_token = null;
        /** @var bool Whether the query has been executed */
        protected $_executed = false;
        /** @var string Type of query **/
        protected $_type = null;
        /** @var string Contains the method to execute */
        protected $_method = null;
        /** @var Variables Contains the variables list */
        protected $_variables = null;
        /** @var DeepList Contains a list of parameters */
        protected $_params = null;
        /** @var DeepList Contains a list of fields */
        protected $_fields = null;

        public function __construct(string $type, string $method, array $fields = [], array $variables = [], array $params = []) {
            $this->_type = $type;
            $this->_method = $method;
            $this->_params = new DeepList($params, true);
            $this->_fields = new DeepList($fields);
            $this->_variables = new Variables($variables);
        }

        /**
         * Sets the project id
         *
         * @param string $project
         * @return Query
         */
        public function setProjectId(string $project) : Query {
            $this->_project = $project;
            return $this;
        }

        /**
         * Sets the token
         *
         * @param string $token
         * @return Query
         */
        public function setToken(string $token) : Query {
            $this->_token = $token;
            return $this;
        }

        /**
         * Returns the variables list
         *
         * @return Variables
         */
        public function variables() : Variables {
            return $this->_variables;
        }

        /**
         * Returns the parameter list
         *
         * @return DeepList
         */
        public function params() : DeepList {
            return $this->_params;
        }

        /**
         * Returns the fields list
         *
         * @return DeepList
         */
        public function fields() : DeepList {
            return $this->_fields;
        }

        /**
         * Builds the query
         *
         * @return string
         */
        public function build() : string {
            // start query
            $query = $this->_type.' method';
            // add variables if any
            if(count($this->_variables)>0) {
                $query .= '('.$this->_variables->build().')';
            }
            $query .= '{';
            // add method
            $query .= $this->_method;
            // add params if any
            if(count($this->_params)>0) {
                $query .= '('.$this->_params->build().')';
            }
            $query .= '{';
            // add field list
            $query .= $this->_fields->build();
            // end query
            $query.='}}';
            // return
            return $query;
        }

        /**
         * Whether the query was already executed
         *
         * @return bool
         */
        public function executed() : bool {
            return $this->_executed;
        }

        /**
         * Creates a request object from the current Query
         *
         * @param array $values The variables to send along
         * @return Request
         */
        protected function request(array $values = []) : Request {
            $query = $this->build();
            // create cURL request
            $curl = new cURL();
            $request = $curl->newJsonRequest('POST', GraphCMS::SIMPLE_URL.$this->_project, [ 
                'operationName' => 'method',
                'query' => $query,
                'variables' => $values
            ])->setHeader('Authorization', 'Bearer '.$this->_token);
            // set request
            $r = new Request();
            $r->setRequest($request);
            return $r;
        }

        /**
         * Executes a query using a project id, a token and
         * an array of values
         *
         * @param string $project The project ID
         * @param string $token The AUTH token
         * @param array $values The array of variables
         *
         * @return Array
         */
        public function execute(array $values = []) : array {
            $cache = $this->getCacheAdapter();
            $request = $this->request($values);
            // get from cache if available
            if(!empty($cache)&&($this->_type!==Query::TYPE_MUTATION)) {
                $expired = false;
                $data = $cache->get($request, $expired);
                // if empty? return synchronously
                if(empty($data)) {
                   $response = $request->send();
                   $cache->set($request, $data);
                   return $data; 
                }
                // if expired call for update and return cached data
                if($expired) {
                    $cache->update($request);
                }
                $this->_executed = true;
                return $data;
            }
            // just execute sychronously
            $this->_executed = true;
            return $request->send();
        }
    }