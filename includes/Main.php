<?php

/**
 * Description of Main
 *
 * @author Cristopher Mendoza
 */
require 'Views.php';

class Main {

    static function run($run) {
        $requestArray = self::processURL($run['url']);
        Views::loadView($requestArray, $run['blogData']);
    }

    static function processURL($url) {
        $splitedURL = explode('/', $url);
        $splitedURL = array_map('strtolower', $splitedURL);
        $requestArray = preg_replace("/[^A-Za-z0-9?_\- ]/", '', $splitedURL);
        return ($requestArray);
    }

}
