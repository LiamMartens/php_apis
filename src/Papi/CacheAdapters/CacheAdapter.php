<?php
    namespace Papi\CacheAdapters;
    use Papi\Request;

    abstract class CacheAdapter {
        const EMIT_UPDATE = 'Papi/CacheAdapters/RedisCache/RedisCache/update';

        /**
         * For getting a value from cache
         *
         * @param Request $query
         * @param array $default
         * @return array
         */
        public abstract function get(Request $request, bool &$expired = null) : array;

        /**
         * For directly setting a value in cache
         *
         * @param Request $query
         * @param array $data
         * @return bool
         */
        public abstract function set(Request $request, array $data) : bool;

        /**
         * For updating a value in cache
         * this could be using an emitter with
         * beanstalk, this could be synchronously...
         *
         * @param Request $request
         * @return bool
         */
        public abstract function update(Request $request) : bool;
    }