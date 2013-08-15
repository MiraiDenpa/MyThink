<div id="think_page_trace" class="fixed" style="display: none;position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:'微软雅黑';">
	<div id="think_page_trace_tab" style="background:white;margin:0;height: 250px;">
	<div id="think_page_trace_tab_tit" style="height:30px;padding: 6px 12px 0;border-bottom:1px solid #ececec;border-top:1px solid #ececec;box-shadow: 0px -3px 4px -2px #F00;">
		<?php
		foreach($trace as $key => $value){
			if($value[1]!='***'){
				$tabbtns[] = "<span class=\"trace_tab_xxx {$value[2]}\" title=\"级别：{$value[1]}\" data-index=\"{$key}\">{$value[0]}</span>";
				$tabbtns[] = '|';
			}else{
				if( ($last = array_pop($tabbtns)) != '|' ) $tabbtns[] = $last;
				$tabbtns[] = '<label class="lbutton label label-info">'.$value[0].'</label>';
			}
		}
		if( ($last = array_pop($tabbtns)) != '|' ) $tabbtns[] = $last;
		echo implode(' ', $tabbtns);
		?>
	</div>
	<div id="think_page_trace_tab_cont" style="overflow:auto;height:212px;padding: 0; line-height: 24px">
		<?php foreach($trace as $key=>$data){
			$info = array_pop($data);
			?>
		<ol class="tab tab<?php echo $key ?>" style="display:none;">
			<?php if(is_array($info)){
				foreach($info as $k => $val){
					echo '<li style="clear:both">'.(is_numeric($k)? '' : $k.' : ').
								$val.'</li>';
				}
			} ?>
		</ol>
		<?php } ?>
	</div>
	</div>
	<div id="think_page_trace_close" style="display:none;text-align:right;height:15px;position:absolute;top:10px;right:12px;cursor: pointer;"><img style="vertical-align:top;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw==" /></div>
</div>
<div class="fixed" id="think_page_trace_open" style="height:30px;float:right;text-align: right;overflow:hidden;position:fixed;bottom:0;right:0;color:#000;line-height:30px;cursor:pointer;">
	<div style="background:#232323;color:#FFF;padding:0 6px;float:right;line-height:30px;font-size:14px">
		<?php echo G('beginTime', 'viewEndTime').'s '; ?>
	</div>
	<img width="30" style="" title="ShowPageTrace" src="data:image/png;base64,<?php echo App::logo() ?>">
</div>
<script type="text/javascript">
try{
	if(typeof $ == 'undefined') {
		var script = document.createElement('script');
		script.type="text/javascript";
		script.src= "<?php echo PUBLIC_URL;?>/jquery/jquery.min.js";
		script.onload = ready;
		document.head.appendChild(script);
	}else{
		$(ready);
	}
}catch(e){
	alert('ThinkPageTrace Error A: \n'+e.toString());
	console.error(e);
}
function ready(){
	try{
		var btnTabs  = $('#think_page_trace_tab_tit .trace_tab_xxx');
		var btnOpen     = $('#think_page_trace_open');
		var btnClose    = $('#think_page_trace_close');
		var trace = $('#think_page_trace');
	
		var lastPage = $('#think_page_trace .lbutton');
		if( lastPage.next().length == 0 ) lastPage.remove();
		lastPage = null;
		
		btnOpen.click(function(){
			btnOpen.hide();
			btnClose.show();
			
			trace.show();
		});
		btnClose.click(function(){
			btnOpen.show();
			btnClose.hide();
			
			trace.hide();
		});
	
		var last = $();
		var lastTab = $();
		var x =$('#think_page_trace_tab').on('click','.trace_tab_xxx',function(i){
			var obj = $(this);
			var the_tab = $('.tab'+obj.data('index'));
			if(last.data('index') === obj.data('index')) return;
			obj.addClass('bold');
			last.removeClass('bold');
			the_tab.show();
			lastTab.hide();
			
			last = obj;
			lastTab = the_tab;
		});
		if(trace.find('.red').length){
			btnOpen.click();
		}
		$(btnTabs[0]).click();
	}catch(e){
		alert('ThinkPageTrace Error B: \n'+e.toString());
		console.error(e);
	}
}
</script>
<style type="text/css">
#think_page_trace .trace_tab_xxx {
	font-weight: 700;
	color: #999;
	line-height: 30px;
	display: inline-block;
	cursor: pointer;
	font-family: '微软雅黑';
	font-size:16px;
}
#think_page_trace .red{
	color: red;
}
#think_page_trace .bold{
	background-color: black;
}
#think_page_trace .lbutton{
	padding: 4px;
	margin-left: 8px;
	margin-right: 5px;
}
#think_page_trace .xdebug-var-dump{
	line-height: 1em;
	margin: 0;
	padding-left: 2em;
}
#think_page_trace li:hover{
	background: rgba(119, 119, 119, 0.38);
}
#think_page_trace,#think_page_trace_open{
	-webkit-user-select: none;
}
#think_page_trace li{
	-webkit-transition: background-color;
	-moz-transition: background-color;
	-ms-transition: background-color;
	-o-transition: background-color;
	transition: background-color;
	-webkit-user-select: text;
}

#think_page_trace_tab_cont ol{
	padding: 0;
	margin:0;
}
#think_page_trace_tab_cont li{
	border-bottom:1px solid #EEE;
	font-size:14px;
	padding:0 12px;
}
</style>
