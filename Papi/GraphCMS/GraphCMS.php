<?php
    namespace Papi\GraphCMS;
    use Papi\CacheAdapters\CacheAdapterAware;
    use Papi\CacheAdapters\CacheAdapter;
    use anlutro\cURL\cURL;
    use anlutro\cURL\Request;

    /**
     * The Type class is a wrapper for setting variable
     * datatypes. It has some common ones, wraps required option
     * and so on
     */
    class Type {
        /**
         * Below are all current supported GraphCMS
         * types. There's also enum, but Enum's are built
         * as follows: MODELNAME_ENUMNAME
         *
         * Location and Json are the same since Location is just
         * a JSON containing lat and lng keys
         *
         * Date and DateTime are the same since they are stored
         * in the same way in GraphCMS
         *
         * Color and String are the same because a color
         * is simply passed as a String like this
         * {"r": 255, "g": 255, "b": 255}
         */
        const ID = 'Id';
        const STRING = 'String';
        const COLOR = 'String';
        const INT = 'Int';
        const JSON = 'Json';
        const LOCATION = 'Json';
        const BOOL = 'Boolean';
        const FLOAT = 'Float';
        const DATE = 'DateTime';
        const DATETIME = 'DateTime';
        const ORDER = 'OrderBy';

        protected $_prefix;
        protected $_type;
        protected $_required;
        protected $_multiple;
        public function __construct(string $type, bool $required = false, bool $multiple = false) {
            $this->_type = $type;
            $this->_required = $required;
            $this->_multiple = $multiple;
            $this->_prefix = '';
        }

        public function isRequired() : Type {
            $this->_required = true;
            return $this;
        }

        public function isMultiple() : Type {
            $this->_multiple = true;
            return $this;
        }

        /**
         * The prefix method can be used to set the prefix
         * which is sometimes necessary (i.e, Enum type, OrderBy type
         * ModelOrderBy)
         *
         * @param string $prefix
         */
        public function prefix(string $prefix) : Type {
            $this->_prefix = $prefix;
            return $this;
        }

        public function __toString() : string {
            $type = $this->_type.($this->_required ? '!' : '');
            if($this->_multiple) {
                return '['.$type.']';
            }
            return $type;
        }
    }

    abstract class L implements \Countable {
        public abstract function build() : string;
        public abstract function __toString() : string;
    }
    /**
     * The variables class is a helper class for building a
     * list of variables and types
     */
    class Variables extends L {
        /** @var array Contains a list of variables and types */
        protected $_variables = [];

        public function __construct(array $vars = []) {
            $this->_variables = $vars;
        }

        public function count() : int {
            return count($this->_variables);
        }

        public function add(string $name, Type $type) : Variables {
            $this->_variables[$name] = $type;
            return $this;
        }

        public function build() : string {
            $build = '';
            foreach($this->_variables as $k => $v) {
                $build .= '$'.$k.':'.$v.',';
            }
            return trim($build, ',');
        }

        public function __toString() : string {
            return $this->build();
        }
    }
    /**
     * The DeepList class is a helper class for building
     * a nested list
     */
    class DeepList extends L {
        /** @var array Contains the list which can be built, should only contain string keys and array or string values (unqoted print) */
        protected $_list = [];
        /** @var bool Whether the subfields are colon separated */
        protected $_is_separated = false;

        public function __construct(array $list = [], bool $is_sep = false) {
            $this->_list = $list;
            $this->_is_separated = $is_sep;
        }

        public function count() : int {
            return count($this->_list);
        }

        public function add(string $name, $sub = null) {
            if(!isset($sub)) {
                $this->_list[] = $name;
            } else {
                $this->_list[$name] = $sub;
            }
            return $this;
        }

        public function build() : string {
            $build = '';
            foreach($this->_list as $k => $v) {
                if(is_string($k)&&is_array($v)) {
                    $build .= $k.($this->_is_separated ? ':' : '').'{'.(new DeepList($v, $this->_is_separated))->build().'}';
                } else if(is_string($k)&&!is_array($v)) {
                    $build .= $k.($this->_is_separated ? ':' : '').$v;
                } else {
                    $build .= $v;
                }
                $build .= ',';
            }
            return trim($build, ',');
        }

        public function __toString() : string {
            return $this->build();
        }
    }

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
            return $request;
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
            if(!empty($cache)) {
                $expired = false;
                $data = $cache->get($request, $expired);
                // if empty? return synchronously
                if(empty($data)) {
                   $response = $request->send();
                   $data = json_decode($response->body, true);
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
            $response = $request->send();
            return json_decode($response->body, true);
        }
    }

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