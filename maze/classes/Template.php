<?php
    
class Template {
    public function __construct() {}
    
    public function styleRoom($room, $doors) {
        $style = array(
            "maze" => array(
                "room" => array(),
                "hint" => $room[0]->hint
            )
        );
        
        foreach ($doors as $door) {
            array_push($style["maze"]["room"], "./door/" . $door->name);
        }        

        return $style;
    }

    public function styleDoor($door) {
        $door = $door[0];
        $style = array(
            "maze" => array(
                "door" => (object) array(
                    "task" => $door->task,
                    "hint" => $door->hint
                )
            )
        );
        
        return $style;
    }
    
}
