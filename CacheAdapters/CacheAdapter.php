<?php
    namespace Papi\CacheAdapters;

    abstract class CacheAdapter {
        /**
         * For getting a value from cache
         *
         * @param string $query
         * @param array $default
         * @return array
         */
        public abstract function get(string $query, bool &$expired = null) : array;

        /**
         * For directly setting a value in cache
         *
         * @param string $query
         * @param array $data
         * @return bool
         */
        public abstract function set(string $query, array $data) : bool;

        /**
         * For updating a value in cache
         * this could be using an emitter with
         * beanstalk, this could be synchronously...
         *
         * @param Query $query
         * @return bool
         */
        public abstract function update(Cacheable $query, array $values = []) : bool;
    }