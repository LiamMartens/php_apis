<?php
    namespace Papi;
    abstract class Request {
        public abstract function fingerprint() : string;
        public abstract function send() : array;
    }