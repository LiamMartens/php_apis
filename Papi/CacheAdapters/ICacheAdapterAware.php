<?php
    namespace Papi\CacheAdapters;
    interface ICacheAdapterAware {
        public function setCacheAdapter(CacheAdapter $adapter);
        public function getCacheAdapter() : CacheAdapter;
    }