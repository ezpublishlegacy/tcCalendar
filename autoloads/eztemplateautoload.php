<?php

$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/monthtojsonoperator.php',
                                    'class' => 'monthtojsonOperator',
                                    'operator_names' => array( 'monthtojson' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/datesfromrepeat.php',
                                    'class' => 'datesfromrepeatOperator',
                                    'operator_names' => array( 'datesfromrepeat' ) );
                                    
                                    
$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/daysinmonth.php',
                                    'class' => 'daysinmonthOperator',
                                    'operator_names' => array( 'daysinmonth' ) );                                    

$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/firstsun.php',
                                    'class' => 'firstsunOperator',
                                    'operator_names' => array( 'firstsun' ) );

?>