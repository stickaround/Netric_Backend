<?php
class CAntObjectSort
{
    var $fieldName;
    var $order; // Deprecated
    var $direction; // replaced order

    function __construct($fieldName, $direction)
    {
        $this->fieldName = $fieldName;
        $this->direction = $direction;
        $this->order = $this->direction;
    }
}
