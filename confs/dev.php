<?php
return array(
    
    "application" => array(
        "debug" => true,
        "logger" => array(
            "dir" => "/home/work/var/log/bigbang/sample/",
            "format" => "[%file%:%line%][%ip%] %message%",
        ),
    ),
    
    "view" => array(
        "compiledPath"      => "/home/work/var/compiled/sample/",
        "compiledExtension" => ".compiled",
    ),
    
    "bcs" => array(
        "host"   => 'bcs.duapp.com',
        "ak"     => 'QkAPgTkquNrTWqcbEMOOvrq7',
        "sk"     => 'zjtQ4GALm3VtTsr4wm38yRpRcSajD0ZI',
        "bucket" => 'bigbang-product-pic-1',
    ),
);
