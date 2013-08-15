<?php

/**
* U3('list') U3('edit',array('id'=>1))
* http://xxxx/CurrentApp/CurrentOp/xxxx_list
* http://xxxx/CurrentApp/CurrentOp/xxxx_edit/id/1
*
* @param string $arg1
* @param array $data
* @param bool $merge 与当前GET混合
* @return string
*/
function UA($arg1,$data = array(),$merge=true){
$act = explode('_', ACTION_NAME);
$actail = explode('_', $arg1);

$del = count($act)-count($actail);
if($del>0){
$result = array_slice($act, 0, count($act)-count($actail) );
$result = array_merge($result,$actail);
}else{
$result = $actail;
}
return URL(APP_NAME, MODULE_NAME.'/'.implode('_',$result), $data, $merge );
}
