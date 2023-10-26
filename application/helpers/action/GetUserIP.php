<?php


class Damco_Action_Helper_GetUserIP extends Zend_Controller_Action_Helper_Abstract
{
	private  $_ip;
	
	function __construct()
	{
	    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	    {
	      $ip=$_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	    {
	      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else
	    {
	      $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    $this->_ip = $ip;
	    unset($ip);
	}
	
	function GetUserIP()
	{
		return $this->_ip;
	}

	 function direct()
	{
		return $this->GetUserIP();
	} /**/
}

