<style type="text/css">
	#think_page_trace_show{
		z-index: 10005;
	}
	.modal-backdrop{
		 z-index: 10001;
	 }
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

	#think_page_trace_small > ul {
		position: absolute;
		right: 0;
		bottom: 30px;
		list-style: none;
		margin: 0;
		z-index: -1;
	}

	#think_page_trace_small > ul > li {
		background-color: gray;
		border: solid #000000 thin;
		-webkit-transform: skewY(-35deg);
		-webkit-transition: 0.5s;
		font-weight: bold;
		text-align: center;
		min-height: 32px;
		min-width: 32px;
		max-height: 32px;
		max-width: 32px;
		cursor: pointer;
	}

	#think_page_trace_small > ul > li:hover {
		-webkit-transform: skewY(0deg);
	}

	#think_page_trace_small > ul > li.think_page_trace_icons_debug {
		background: <?php echo !isset($_COOKIE['XDEBUG_SESSION'])?'rgb(255, 94, 94)':'rgb(77, 162, 77)';?>;
	}

	#think_page_trace_small > ul > li.think_page_trace_icons_error {
		background: <?php echo isset($debug['NOTIC'])?'rgb(255, 94, 94);':'rgb(77, 162, 77)';?>;
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

	#think_page_trace_content {
		overflow: auto;
	}

	#think_page_trace_content ul {
		list-style: none;
		padding: 0;
		margin: 1px;
	}

	#think_page_trace_content li {
		display: inline;
		margin-left: 20px;
		cursor: pointer;
	}

	#think_page_trace_content li.ajaxbtn.active {
		background: #C5D3FD;
	}

	#think_page_trace_content .ajaxline {
		display: none;
	}

	#think_page_trace_content .tab {
		width: 100%;
		font-size: 12px;
	}

	#think_page_trace_content .tab td {
		padding: 2px;
	}

	#think_page_trace_content .tab td:last-child {
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
				echo "\t<td>" . ($message[0]) . "</td>\n";
				echo "\t<td style=\"width: 100%;\">" . nl2br($message[1]) . "</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody></table>\n";
		} ?>
	</div>
</div>

<div id="think_page_trace_small">
	<ul id="think_page_note_list">
		<li class="think_page_trace_icons_error" title="错误数"><?php echo count($debug['NOTIC']); ?></li>
		<li class="think_page_trace_icons_debug" title="调试模式：<?php echo $_COOKIE['XDEBUG_SESSION']; ?>">
			<?php echo isset($_COOKIE['XDEBUG_SESSION'])? 'Y' : 'N'; ?>
		</li>
	</ul>
	<div id="think_page_trace_open">
		<?php echo G('beginTime', 'viewEndTime') . 's '; ?>
	</div>
</div>
<div id="think_page_trace_show" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">调用堆栈</h4>
			</div>
			<div id="think_page_trace_show_body" class="modal-body">
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary">关闭</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
	try{
		if(typeof $ == 'undefined'){
			var script = document.createElement('script');
			script.type = "text/javascript";
			script.src = "<?php echo PUBLIC_URL;?>/jquery/jquery.js";
			script.onload = ready;
			document.head.appendChild(script);
		} else{
			ready();
		}
	} catch(e){
		alert('ThinkPageTrace Error [jquery fatal error]: \n' + e.toString());
		console.error(e);
	}
	function ready(){
		if(0 == $('link').length){ // 没有css
			$('head').append('<link href="<?php echo PUBLIC_URL;?>/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css">').append('<script src="<?php echo PUBLIC_URL;?>/bootstrap/js/bootstrap.js" type="text/javascript"></'+'script>');
		}
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
			var tab_buttons = $('#think_page_trace_tab').on('click', 'li', function (){
				tab_buttons.find('.active').removeClass('active');
				$(this).addClass('active');
				var tab = '.tab_' + $(this).data('level');
				ul.hide();
				ul.filter(tab).show();
			});
			tab_buttons.first().click();

			var notes = $('#think_page_note_list');
			var ajaxNote, ajaxContent, ajaxButtons;
			var lockAjax = false, lastAjax;

			function showindex(){
				ajaxContent.find('tr:not(.ajaxline_' + lastAjax + ')').hide();
				ajaxContent.find('tr.ajaxline_' + lastAjax).show();
			}

			$(document).ajaxSuccess(function (e, xhr, opt){
				if(lockAjax){
					return;
				}
				if(!ajaxNote){
					$('#think_page_trace_tab .nav').append('<li data-level="AJAX"><a href="javascript:void(0);">AJAX</a></li>');
					var aTabel = $('<table class="table table-hover table-condensed tab tab_AJAX"><tbody></tbody></table>');
					aTabel.appendTo($('#think_page_trace_content')).append();
					ajaxButtons = $('<thead><tr><td colspan="2"><ul></ul></td></tr></thead>').prependTo(aTabel).find('ul');
					ajaxContent = aTabel.find('tbody');

					ajaxNote = $('<li>').prependTo(notes).addClass('think_page_trace_icons_error').attr('title', 'ajax错误数，点击锁定').click(function (){
						lockAjax = !lockAjax;
						if(lockAjax){
							ajaxNote.css('background', 'rgb(255, 94, 94)').attr('title', 'ajax错误数，点击解锁');
						} else{
							ajaxNote.css('background', '').attr('title', 'ajax错误数，点击锁定');
						}
					});

					ajaxButtons.on('click', '.ajaxbtn', function (){
						ajaxButtons.find('.ajaxbtn').removeClass('active');
						lastAjax = $(this).addClass('active').data('index');
						showindex();
					});
				}
				if(xhr.responseJSON && xhr.responseJSON._PAGE_TRACE_){
					var trace = xhr.responseJSON._PAGE_TRACE_;
					ajaxNote.text(trace.NOTIC? trace.NOTIC.length : 0);
					var buttons = '';
					ajaxContent.html('');
					for(var title in trace){
						buttons += '<li class="ajaxbtn" data-index="' + title + '">' + title + '</li>';
						for(var label in trace[title]){
							$('<tr class="ajaxline ajaxline_' + title +
							  '"></tr>').append($('<td></td>').html(trace[title][label][0])).append($('<td style="width: 100%;"></td>').html(trace[title][label][1])).appendTo(ajaxContent);
						}
					}
					ajaxButtons.html(buttons);
					showindex();
				} else{
					ajaxNote.text('?');
				}
			});

			var trace_show = $('#think_page_trace_show');
			var trace_body = $('#think_page_trace_show_body');
			window.trace_show = function(obj){
				trace_body.html($(obj).data('trace'));
				trace_show.modal();
			};
		} catch(e){
			alert('ThinkPageTrace Error [later init error]: \n' + e.toString());
			console.error(e);
		}
	}
</script>
