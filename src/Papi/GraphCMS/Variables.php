<?php
    namespace Papi\GraphCMS;

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