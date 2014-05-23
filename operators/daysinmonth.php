<?php

class daysinmonthOperator
{

    function daysinmonthOperator( $name = "daysinmonth" )
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
        return array( 'daysinmonth' => array('calendar' => array( 'type' => 'integer', 'required' => true, 'default' => CAL_GREGORIAN ),
 												 'year' => array( 'type' => 'integer', 'required' => true, 'default' => (int)date('Y')) ) );
    }

    function modify( $tpl, &$operatorName, &$operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, &$namedParameters )
    {
        $operatorValue = cal_days_in_month($namedParameters['calendar'], $operatorValue, $namedParameters['year']);
		return true;
    }

}

?>
