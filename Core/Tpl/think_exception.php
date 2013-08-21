<!DOCTYPE html>
<head>
<?php
$head = '';
$head .= HTML::importFile(searchPublic('bootstrap.css'));
$head .= HTML::css(PUBLIC_URL.'/artDialog/skins/'.art_skin().'.css');

$head .= HTML::importFile(searchPublic('jquery.js'));
$head .= HTML::importFile(searchPublic('bootstrap.js'));
$head .= HTML::importFile(searchPublic('jquery.artDialog.js'));
$head .= HTML::importFile(searchPublic('artDialog.plugins.js'));

echo $head;

if(is_array($msg)){ // old-fix
	$trace[] = $msg;
	extract($msg, EXTR_OVERWRITE);
	$msg = $message;
}
?>
</head>
<body>
<div class="container">
	<div class="hero-unit fixed" style="margin-top:180px;">
		<h1>发生错误</h1>
		<h5><?php echo nl2br(htmlspecialchars($msg)); ?></h5>
		<table style="width: 100%;">
			<thead>
			<tr>
				<th colspan="2">
					<a href='<?php echo xdebug_filepath($file, $line); ?>'><?php echo $file . ":" . $line; ?></a>
				</th>
			</tr>
			</thead>
			<?php
			foreach($trace as $k => $item){
				echo '<tr><td style="vertical-align: top;">';
				if(isset($item['file'])){
					echo '<a href=\'' . xdebug_filepath($item['file'], isset($item['line'])? $item['line'] : 0) . '\'>';
					echo $item['file'];
					echo isset($item['line'])? $item['line'] : '???';
					echo '</a>';
				} else{
					echo isset($item['file'])? $item['file'] : 'Unknown File';
					echo isset($item['line'])? $item['line'] : '???';
				}

				echo '</td><td style="text-align: right;">';
				echo isset($item['type'])? $item['class'] . $item['type'] . $item['function'] : $item['function'];
				echo '(';
				if(!empty($item['args'])){
					echo '<a href="javascript: void(0);" onclick="$(this).next().slideToggle()">...</a>';
				}
				echo ')<div style="display: none;text-align: left;">';
				if(!empty($item['args'])){
					$args = [];
					foreach($item['args'] as $argi){
						$args[] = htmlspecialchars(dump_some($argi));
					}
					echo '<pre>';
					echo implode("\n", $args);
					echo '</pre>';
				}
				echo '</div></td></tr>';
			}

			?>
		</table>
		<p>
			<a href="/" class="btn btn-large btn-primary">&lt;&lt; 回首页</a>
		</p>
	</div>
</div>
</body>
