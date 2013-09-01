<!DOCTYPE html>
<head>
	<?php
	$head = '';
	$head .= HTML::importFile(searchPublic('bootstrap.css'));
	$head .= HTML::css(PUBLIC_URL . '/artDialog/skins/' . art_skin() . '.css');

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
	<style>
		@media screen and (min-width: 768px){
			.jumbotron h1 {
				font-size: 56px;
			}
		}
	</style>
</head>
<body>
<div class="container">
	<div class="jumbotron fixed" style="margin:40px;">
		<div class="clearfix row">
			<h1 class="col-md-4">发生错误</h1>

			<div class="col-md-8">
				<h3>
					<span class="glyphicon glyphicon-warning-sign" style="margin-right: 5px;"></span><?php echo $html? $msg : nl2br(htmlspecialchars($msg)); ?>
				</h3>
				<a href='<?php echo xdebug_filepath($file, $line); ?>'><?php echo $file . ":" . $line; ?></a>
			</div>
		</div>

		<div class="row">
			<table class="col-md-12">
				<?php
				foreach($trace as $k => $item){
					echo '<tr><td style="vertical-align: top;">';
					if(isset($item['file'])){
						echo '<a href=\'' . xdebug_filepath($item['file'], isset($item['line'])? $item['line'] : 0) .
							 '\'>';
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
							$args[] = dump_some($argi);
						}
						echo '<pre>';
						echo implode("\n", $args);
						echo '</pre>';
					}
					echo '</div></td></tr>';
				}

				?>
			</table>

			<p  class="col-md-12 panel panel-success">
				<a href="/" class="btn btn-large btn-primary">&lt;&lt; 回首页</a>
			</p>
		</div>
	</div>
</div>
</body>
