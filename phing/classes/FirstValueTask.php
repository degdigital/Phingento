<?php
/**
 * Reduces a comma separated property to it's first value
 */
class FirstValueTask extends Task
{
    private $property;
    private $destination;

    function setProperty($property) {
        $this->property = $property;
    }

    function setDestination($destination) {
        $this->destination = $destination;
    }

    function main() {
        $value = $this->project->getProperty($this->property);
        $comma = strpos($value, ',');
        if($comma !== false) {
            $value = substr($value, 0, $comma);
        }

        if(!$this->destination) {
            $this->destination = $this->property;
        }

        $this->project->setProperty($this->destination, $value);
    }
}