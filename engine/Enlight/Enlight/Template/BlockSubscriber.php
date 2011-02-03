<?php
interface Enlight_Template_BlockSubscriber
{
    /**
     * Returns an array of events that this subscriber listens 
     *
     * @return array
     */
    public function getSubscribedBlocks();
}