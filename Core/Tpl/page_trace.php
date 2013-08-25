<script type="text/javascript">
	try{
		if(typeof $ == 'undefined'){
			var script = document.createElement('script');
			script.type = "text/javascript";
			script.src = "<?php echo PUBLIC_URL;?>/jquery/jquery.js";
			script.onload = ready;
			document.head.appendChild(script);
		} else{
			$(ready);
		}
	} catch(e){
		alert('ThinkPageTrace Error [jquery fatal error]: \n' + e.toString());
		console.error(e);
	}
	function ready(){
		try{
			var cnt = $('#think_page_trace');
			var cnt_inner = $('#think_page_trace_content');
			var tabHeight;
			var open = $('#think_page_trace_small');
			$('#think_page_trace_open').click(function (){
				cnt.show();
				open.hide();
				tabHeight = $('#think_page_trace_tab').height();
				cnt_inner.css('height', cnt.height() - tabHeight);
			});
			$('#think_page_trace_close').click(function (){
				cnt.hide();
				open.show();
				return false;
			});
			
			function resize_trace(e){
				var height = window.innerHeight - e.pageY + $(window).scrollTop();
				cnt.css('height', height);
				cnt_inner.css('height', height - tabHeight);
			}

			$('#think_page_trace_sizeable').mousedown(function (e){
				$(document).on('mousemove', resize_trace).on('mouseup', function (e){
					$(document).off('mousemove', resize_trace);
					$(document).off('mouseup', arguments.callee);
				});
			});

			var ul = $('#think_page_trace_content .tab');
			var tab_buttons = $('#think_page_trace_tab>ul>li').click(function (){
				tab_buttons.removeClass('active');
				$(this).addClass('active');
				var tab = '.tab_' + $(this).data('level');
				ul.hide();
				ul.filter(tab).show();
			});
			tab_buttons.first().click();;
		} catch(e){
			alert('ThinkPageTrace Error [later init error]: \n' + e.toString());
			console.error(e);
		}
	}
</script>
<style type="text/css">
	#think_page_trace {
		z-index: 10000;
		-webkit-user-select: none;
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		height: 40%;
		display: none;
		background-color: white;
		box-shadow: 0 -5px 5px rgba(136, 136, 136, 0.32);
		overflow: visible;
	}

	#think_page_trace_open {
		color: #FFF;
		background-color: #232323;
		background-image: url('data:image/png;base64,<?php echo base64_encode(file_get_contents(THINK_PATH.'logo_30.png'));?>');
		background-repeat: no-repeat;
		padding: 2px 10px 0 38px;
		border-top-left-radius: 7px;
		cursor: pointer;
	}
	#think_page_trace_small {
		line-height: 30px;
		font-size: 14px;
		z-index: 9999;
		position: fixed;
		bottom: 0;
		right: 0;
	}
	#think_page_trace_small > ul{
		position: absolute;
		right: 5px;
		bottom: 34px;
		list-style: none;
		padding: 0;
		margin: 0;
	}
	#think_page_trace_small > ul > li{
		text-align: center;
		min-height: 32px;
		min-width: 32px;
		max-height: 32px;
		max-width: 32px;
	}
	
	#think_page_trace_debug{
		background-color: <?php echo isset($_COOKIE['XDEBUG_SESSION'])?'rgba(77, 162, 77, 0.59);':'gray';?>;
		content: "<?php echo isset($_COOKIE['XDEBUG_SESSION'])?'YES':'NO';?>";
		border-radius: 16px;
		font-weight: bold;
	}

	#think_page_trace_close {
		float: right;
	}

	#think_page_trace_sizeable {
		z-index: 10001;
		position: absolute;
		left: 0;
		width: 100%;
		top: -5px;
		height: 5px;
		cursor: ns-resize;
	}

	#think_page_trace_content{
		overflow: auto;
	}
	#think_page_trace_content ul {
		list-style: none;
		padding: 0;
		margin: 0;
		display: none;
	}

	#think_page_trace_content li {
		border-bottom: black 1px solid;
	}
	#think_page_trace_content .tab{
		width: 100%;
		font-size: 12px;
	}
	#think_page_trace_content .tab td{
		padding: 2px;
	}
	#think_page_trace_content .tab td:last-child{
		-webkit-user-select: all;
	}
</style>
<div id="think_page_trace">
	<div id="think_page_trace_sizeable"></div>
	<div id="think_page_trace_tab">
		<ul class="nav nav-tabs">
			<?php foreach($debug as $level => $log){ ?>
				<li data-level="<?php echo $level; ?>">
					<a href="javascript:void(0);"><?php echo $level; ?></a>
				</li>
			<?php } ?>
			<li class="pull-right">
				<a href="javascript:void(0);" id="think_page_trace_close">[close]</a>
			</li>
		</ul>
	</div>
	<div id="think_page_trace_content">
		<?php foreach($debug as $level => $log){
			echo '<table class="table table-hover table-condensed tab tab_' . $level . '"><tbody>';
			foreach($log as $message){
				echo "\n<tr>\n";
				echo "\t<td>".($message[0])."</td>\n";
				echo "\t<td style=\"width: 100%;\">".nl2br($message[1])."</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody></table>\n";
		} ?>
	</div>
</div>

<div id="think_page_trace_small">
	<ul>
		<li>aaa</li>
		<li title="调试模式：<?php echo $_COOKIE['XDEBUG_SESSION'];?>" id="think_page_trace_debug">D</li>
	</ul>
	<div id="think_page_trace_open">
		<?php echo G('beginTime', 'viewEndTime') . 's '; ?>
	</div>
</div>
