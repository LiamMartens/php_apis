<?php
    namespace Papi\CacheAdapters;
    use Curl\Curl;

    abstract class CacheAdapter {
        const EMIT_UPDATE = 'Papi/CacheAdapters/RedisCache/RedisCache/update';

        /**
         * For getting a value from cache
         *
         * @param string $query
         * @param array $default
         * @return array
         */
        public abstract function get(string $request, bool &$expired = null) : array;

        /**
         * For directly setting a value in cache
         *
         * @param string $query
         * @param array $data
         * @return bool
         */
        public abstract function set(string $request, array $data) : bool;

        /**
         * For updating a value in cache
         * this could be using an emitter with
         * beanstalk, this could be synchronously...
         *
         * @param Query $query
         * @return bool
         */
        public abstract function update(Curl $request) : bool;
    }