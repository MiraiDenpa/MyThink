<?php
class FallbackAction{
	function __construct(Dispatcher $dis){
		_404(LANG_ACTION_NOT_EXIST . ':' . $dis->action_name);
	}
}
