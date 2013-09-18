<?php

$FunctionList = array();

$FunctionList['events'] = array( 'name' => 'events',
                                'call_method' => array('class'=>'tCCalendarEventFunctions', 'method'=>'fetchEvents'),
                                'parameter_type' => 'standard',
                                'parameters' => array(	array(
								'name'     => 'from_date',
								'type'     => 'int',
								'required' => false,
								'default'  => false
							),
							array(
								'name'     => 'to_date',
								'type'     => 'int',
								'required' => false,
								'default'  => false
							),
							array(
								'name'     => 'offset',
								'type'     => 'int',
								'required' => false,
								'default'  => 0
							),
							array(
								'name'     => 'limit',
								'type'     => 'int',
								'required' => false,
								'default'  => 10
							),
							array(
								'name'     => 'query',
								'type'     => 'int',
								'required' => false,
								'default'  => false
							),
							array(
								'name'     => 'filters',
								'type'     => 'string',
								'required' => false,
								'default'  => false
							),
							array(
								'name'     => 'sort_by',
								'type'     => 'string',
								'required' => false,
								'default'  => "Date"
							),
							array(
								'name'     => 'parent_node',
								'type'     => 'int',
								'required' => true,
								'default'  => 2
							)   
                                                     ) );

?>
