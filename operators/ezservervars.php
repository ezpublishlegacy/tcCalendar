<?php

class ServerVarsOperator
{
	var $Operators;

	function __construct(){
		$this->Operators=array('servervars', 'postvars', 'getvars','ezservervars', 'ezpostvars', 'ezgetvars');
	}

	function &operatorList(){
		return $this->Operators;
	}

	function namedParameterPerOperator(){
		return array();
	}

	function namedParameterList(){
		return array();
	}

	function modify(&$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters){
		switch($operatorName){
			case 'postvars':
			case 'ezpostvars':
				$operatorValue=$_POST;
				break;
			case 'getvars':
			case 'ezgetvars':
				$operatorValue=$_GET;
				break;
			default:
				$operatorValue=$_SERVER;
		}
		return false;
	}
}

?>