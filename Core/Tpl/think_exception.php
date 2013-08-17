<?php
echo StandardHeader();
if(is_array($msg)){
	$trace[] = $msg;
	extract($msg, EXTR_OVERWRITE);
	$msg = $message;
}
?>
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
				$args = [];
				echo '<tr><td>';
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
				echo '(<a href="#' . $k . '">...</a>)<div style="display: none;text-align: left;">';
				if(isset($item['args'])){
					foreach($item['args'] as $argi){
						$args[] = htmlspecialchars($argi);
					}
				}
				echo implode("\n", $args);
				echo '</div></tr></td>';
			}

			?>
		</table>
		<p>
			<a href="/" class="btn btn-large btn-primary">&lt;&lt; 回首页</a>
		</p>
	</div>
</div>
</body>
<?php
echo StandardFooter();
?>
