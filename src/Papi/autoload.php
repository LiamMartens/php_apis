<?php
    # for non composer installation
    spl_autoload_register(function($class) {
        if(substr($class, 0, 5)=='Papi\\') {
           $fileName = str_replace('\\', '/', __DIR__.'/'.substr($class, 5).'.php');
           if(is_file($fileName)) {
               require $fileName;
           }
        }
     });