<?php
    
class Template {
    public function __construct() {}
    
    public function styleRoom($room, $doors) {
        $style = array(
            "room" => array(
                "doors" => array()
            )
        );
        
        foreach($room[0] as $key => $val) {
            if (!in_array($key, ["name", "doors"])) {
                $style["room"][$key] = $val;
            }
        }
        
        foreach ($doors as $door) {
            array_push($style["room"]["doors"], "./door/" . $door->name);
        }        

        return $style;
    }

    public function styleDoor($door) {
        $door = $door[0];
        $door_properties = array();

        foreach ($door as $key => $val) {
            if (!in_array($key, ["name", "key", "redirect"])) {
                $door_properties[$key] = $val;
            }
        }
        $style = array(
            "door" => (object) $door_properties
        );
        
        return $style;
    }
    
}
