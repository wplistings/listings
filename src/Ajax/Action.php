<?php

namespace Listings\Ajax;

abstract class Action
{
    /**
     * Get the action string that this Ajax action will respond to. Will be prefixed
     * by the handler, so just providing the action string itself is enough.
     * @return string
     */
    abstract public function getActionString();

    /**
     * Method that is called when the action with this action string is called. Usually
     * this is the moment where the Ajax request is posted to the endpoint.
     */
    abstract public function doAction();
}