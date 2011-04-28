<?php

interface  avsplugin
{
	public function name();
	public function image();
	public function showAdmin();
	public function showUser();
	public function validate();		
}

?>