<?php if(0){
	$debug = array();
}?>
<div id="think_page_trace">
	<div id="think_page_trace_sizeable"></div>
	<div id="think_page_trace_tab">
		<ul>
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
	<hr style="margin-top: 0;"/>
	<div id="think_page_trace_content">
		<?php foreach($debug as $level => $log){
			echo '<ul class="tab_' . $level . '">';
			foreach($log as $label => $message){
				?>
				<li>
					<span><?php echo $label; ?></span>&nbsp;:&nbsp;
					<span><?php echo $message; ?></span>
				</li>
			<?php
			}
			echo '</ul>';
		} ?>
	</div>
</div>
<div id="think_page_trace_open">
	<?php echo G('beginTime', 'viewEndTime') . 's '; ?>
</div>

<script type="text/javascript">
	try{
		if(typeof $ == 'undefined'){
			var script = document.createElement('script');
			script.type = "text/javascript";
			script.src = "<?php echo PUBLIC_URL;?>/jquery/jquery.min.js";
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
			var open = $('#think_page_trace_open').click(function (){
				cnt.show();
				$(this).hide();
			});
			$('#think_page_trace_close').click(function (){
				cnt.hide();
				open.show();
			});

			function resize_trace(e){
				console.log('' + window.innerHeight + ' - ' + e.pageY + ' - ' + $(window).scrollTop() + ' = ' +
							(window.innerHeight - e.pageY));
				cnt.css('height', window.innerHeight - e.pageY + $(window).scrollTop());
			}

			$('#think_page_trace_sizeable').mousedown(function (e){
				$(document).on('mousemove', resize_trace).on('mouseup', function (e){
					$(document).off('mousemove', resize_trace);
					$(document).off('mouseup', arguments.callee);
				});
			});

			var ul = $('#think_page_trace_content ul');
			$('#think_page_trace_tab>ul>li').click(function (){
				var tab = '.tab_' + $(this).data('level');
				ul.hide();
				ul.filter(tab).show()
			});
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
		z-index: 9999;
		background-color: #232323;
		color: #FFF;
		background-image: url('data:image/png;base64,<?php echo base64_encode(file_get_contents(THINK_PATH.'logo_30.png'));?>');
		background-repeat: no-repeat;
		line-height: 30px;
		font-size: 14px;
		position: fixed;
		padding: 2px 10px 0 38px;
		bottom: 0;
		right: 0;
		border-top-left-radius: 7px;
		cursor: pointer;
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

	#think_page_trace_tab ul {
		list-style: none;
		padding: 0;
		margin: 4px;
	}

	#think_page_trace_tab li {
		display: inline;
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
</style>
