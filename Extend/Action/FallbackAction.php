<?php
class FallbackAction{
	function __construct(Dispatcher $dis){
		_404(LANG_ACTION_NOT_EXIST . ':' . ACTION_PREFIX . $dis->action_name. 'Action');
	}
}
