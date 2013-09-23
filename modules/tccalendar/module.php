<?php

$Module = array( 'name' => 'tccalendar',
                 'variable_params' => true );

$ViewList = array();

$ViewList['monthtojson'] = array(
    'script' => 'monthtojson.php',
    'params' => array( 'ParentNodeID') );

$ViewList['test'] = array(
    'script' => 'test.php',
    'params' => array() );

$ViewList['upcoming'] = array(
    'script' => 'upcoming.php',
    'params' => array( 'cal_id', 'from_time', 'to_time') );

$Language = array(
    'name'=> 'Language',
    'values'=> array(),
    'path' => 'classes/',
    'file' => 'ezcontentlanguage.php',
    'class' => 'eZContentLanguage',
    'function' => 'fetchLimitationList',
    'parameter' => array( false )
    );

?>