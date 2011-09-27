<?php

class monthtojsonOperator
{

    function monthtojsonOperator( $name = "monthtojson" )
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
        return array( 'asort' => array('first_param' => array( 'type' => 'integer', 'required' => false, 'default' => 2 ) ) );
    }

    function modify( $tpl, &$operatorName, &$operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, &$namedParameters )
    {
		$tc = new tcCalendar($operatorValue);
		$tc->monthtojson();
		$operatorValue = '';
		return true;
    }

}

?>
