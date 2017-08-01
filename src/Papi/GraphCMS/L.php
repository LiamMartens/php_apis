<?php
    namespace Papi\GraphCMS;
    abstract class L implements \Countable {
        public abstract function build() : string;
        public abstract function __toString() : string;
    }