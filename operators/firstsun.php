<?php

class firstsunOperator
{

    function firstsunOperator( $name = "firstsun" )
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
        return array( 'firstsun' => array() );
    }

    function modify( $tpl, &$operatorName, &$operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, &$namedParameters )
    {
        $dow = date('w',$operatorValue);
        $operatorValue = strtotime("- $dow days", $operatorValue);
		return true;
    }

}

?>
