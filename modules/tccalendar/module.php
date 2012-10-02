<?php
$Module = array( 'name' => 'tccalendar',
                 'variable_params' => true );

$ViewList = array();

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