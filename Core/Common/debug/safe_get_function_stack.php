<?php
function safe_get_function_stack(){
	$html  = '<div class="panel-group" id="think_page_trace_accordion">';
	$trace = xdebug_get_function_stack();
	array_pop($trace);
	foreach($trace as $index => $level){
		if(isset($level['type'])){
			$title = $level['class'] . ($level['type'] == 'dynamic'? '->' : '::') . $level['function'];
		} else{
			$title = $level['function'] . '()';
		}
		$fn = xdebug_filepath_anchor($level['file'], $level['line']);
		if(empty($level['params'])){
			$html .= <<<PHP
	<div class="panel panel-default">
		<div class="panel-heading"><h4 class="panel-title">
			<a class="accordion-toggle">
			{$title}()
			</a><span class="pull-right">{$fn}</span>
		</h4></div>
	</div>
PHP;
		} else{
			$params = '';
			foreach($level['params'] as $name => $value){
				$params .= "<div>{$name} : {$value}</div>";
			}
			$html .= <<<PHP
	<div class="panel panel-default">
		<div class="panel-heading"><h4 class="panel-title">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#think_page_trace_accordion" href="#think_page_trace_collapse{$index}">
			{$title}(<i>...</i>)
			</a><span class="pull-right">{$fn}</span>
		</h4></div>
		<div id="think_page_trace_collapse{$index}" class="panel-collapse collapse">
			<div class="panel-body">
				{$params}
			</div>
		</div>
	</div>
PHP;
		}
	}
	return str_replace("\n", '', $html) . '</div>';
}

?>
