<?php
    namespace Papi\GraphCMS;
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