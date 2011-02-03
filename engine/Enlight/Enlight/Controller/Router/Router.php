<?php
abstract class Enlight_Controller_Router_Router extends Enlight_Class
{
	protected $front;
	public function setFront(Enlight_Controller_Front $front)
    {
        $this->front = $front;
        return $this;
    }
}