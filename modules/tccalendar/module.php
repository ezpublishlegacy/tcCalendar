<?php
$Module = array( 'name' => 'tccalendar',
                 'variable_params' => true );

$ViewList = array();

$ViewList['monthtojson'] = array(
    'script' => 'monthtojson.php',
    'params' => array( 'ParentNodeID') );

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