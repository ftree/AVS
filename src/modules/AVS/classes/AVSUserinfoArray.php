<?php

class AVSUserinfoArray extends PNObjectArray
{
    // The constructor only needs to set the fields which are used
    // to configure the object's specific properties and actions.
    // For the most part we can
    function AVSUserinfoArray($init=null, $where='')
    {
        // Call base-class constructor
        $this->PNObjectArray();
        $this->_objType  = 'AVS_userinfo';
        $this->_objPath  = 'AVSUserinfo';

        // Call initialization routing
        $this->_init($init, $where);
    }

}
?>