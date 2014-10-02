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
        return array( 'monthtojson' => array('first_param' => array( 'type' => 'array', 'required' => false, 'default' => array() ) ) );
    }

    function modify( $tpl, &$operatorName, &$operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, &$first_param )
    {
		$tc = new tcCalendar($operatorValue, null, null, $first_param['first_param']);
		$operatorValue = $tc->monthtojson();
		return true;
    }

}

?>
