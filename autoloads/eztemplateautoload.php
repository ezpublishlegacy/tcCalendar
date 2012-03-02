<?php

$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/monthtojsonoperator.php',
                                    'class' => 'monthtojsonOperator',
                                    'operator_names' => array( 'monthtojson' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/tccalendar/operators/datesfromrepeat.php',
                                    'class' => 'datesfromrepeatOperator',
                                    'operator_names' => array( 'datesfromrepeat' ) );

?>