<?

$ModuleTools = ModuleTools::initialize($Params);

$ModuleTools->setVariable('pagelayout', false);

$Params = array();
$Params["ClassName"] = 'ezfModuleFunctionCollection';
$Params["FunctionName"] = 'search';

$ClassName = $Params["ClassName"];

$r=new ReflectionMethod(new $ClassName, $Params["FunctionName"]);
$params = $r->getParameters();

$func_params = array();

foreach ($params as $param) {
	if (array_key_exists($param->name, $_GET)) {
		$val = $_GET[$param->name];
		if ($val == 'true' || $val == 'false' || $val == 'null' || is_numeric($val)) {
			eval("\$val = $val;");
		} elseif (is_array(@unserialize(urldecode($val)))) {
			$val = @unserialize(urldecode($val));
		} else {
			$val = urldecode($val);
		}
		$func_params[] = $val;
	} else {
		$func_params[] = $param->getDefaultValue();
	}
}

$res = call_user_func_array(array(new $ClassName, $Params["FunctionName"]), $func_params);

//$GLOBALS['eZDebugEnabled']=false;

return $ModuleTools->fetchResult(array(
	'variables' => array(
		'results' => $res
	)
));


?>