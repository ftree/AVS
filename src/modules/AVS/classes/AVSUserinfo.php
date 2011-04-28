<?php
class AVSUserinfo extends PNObject
{

    /**
     * Constructur, init everything
     */
	function AVSUserinfo($init=null, $key=null)
    {
        $this->PNObject(); // call parent constructor
        $this->_objType     = 'AVS_userinfo';
        $this->_objPath  	= 'AVSUserinfo';

        $this->_init($init, $key);
    }

}
?>