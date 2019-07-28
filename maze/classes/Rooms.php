<?php

class Rooms {

    protected $data;

    public function __construct() {
        $this->data = json_decode(file_get_contents(__DIR__ . '/../data/data.json'));
    }

    private function _search_objects($objects, $key, $value) {
        $return = array();
        foreach ($objects as $object) {
            $objVars = get_object_vars($object);
            if (isset($objVars[$key]) && $objVars[$key] == $value) {
                $return[] = $object;
            }
        }
        return $return;
    }

    public function getRoom($routes) {
        $data = $this->data;
        return $this->_search_objects($data->rooms, 'name', $routes[1]);
    }

    public function getDoor($routes, $doors) {
        return $this->_search_objects($doors, 'name', $routes[3]);
    }

    public function checkAnswer($answer, $door) {
        $door = $door[0];
        return $door->key === $answer ? true : false;
    }
}
