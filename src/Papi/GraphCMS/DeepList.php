<?php
    namespace Papi\GraphCMS;

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