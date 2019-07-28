<?php

class Routing {

    protected $uri;
    protected $query_strings;

    public function __construct() {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->query_strings = array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : null;
    }

    public function getUriTokens() {
        $params = array();
        if ($this->query_strings)
            parse_str($this->query_strings, $params);
        return $params;
    }

    public function getCurrentUri() {
        $uri = $this->uri;

        if (strstr($this->uri, '?'))
            $uri = substr($uri, 0, strpos($uri, '?'));

        return trim($uri, '/');
    }

    public function getRoutesArray() {
        return explode('/', $this->getCurrentUri());
    }

}
