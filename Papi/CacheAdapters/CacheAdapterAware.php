<?php
    namespace Papi\CacheAdapters;

    abstract class CacheAdapterAware implements ICacheAdapterAware {
        /** @var CacheAdapter Contains the current cache adapter */
        protected $_cache;

        public function setCacheAdapter(CacheAdapter $adapter) {
            $this->_cache = $adapter;
        }

        public function getCacheAdapter() : CacheAdapter {
            return $this->_cache;
        }
    }