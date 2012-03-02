<?php

class datesfromrepeatOperator
{

    function datesfromrepeatOperator( $name = "datesfromrepeat" )
    {
	$this->Operators = array( $name );
    }


    function &operatorList()
    {
	return $this->Operators;
    }


    function namedParameterPerOperator()
    {
        return true;
    }   

    function namedParameterList()
    {
        return array( 'datesfromrepeat' => array('start_time' => array( 'type' => 'integer', 'required' => true, 'default' => time() ),
 												 'end_time' => array( 'type' => 'integer', 'required' => false, 'default' => null) ) );
    }

    function modify( $tpl, &$operatorName, &$operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, &$namedParameters )
    {
		if (!method_exists($operatorValue, 'get_timestamps')) {
			$operatorValue = array();
			return false;
		}
		$operatorValue = $operatorValue->get_timestamps($namedParameters['start_time'], $namedParameters['end_time']);

		return true;
    }

}

?>
