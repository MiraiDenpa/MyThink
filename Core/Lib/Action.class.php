<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP Action控制器基类 抽象类
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
abstract class Action{
	/**
	 * 当前控制器名称
	 * @var string name
	 */
	private $name = '';

	/**
	 * @var Dispatcher
	 */
	protected $dispatcher = null;

	/**
	 * @var array
	 */
	protected $data;

	protected $tVar = [];
	
	/**
	 * FIXME ugly code
	 *
	 * @param Dispatcher $new
	 *
	 * @return void
	 */
	public function setDispatcher(&$new){
		$this->dispatcher = & $new;
	}

	public function setData(&$new){
		$this->data = & $new;
	}

	public function getData($name){
		return $this->data[$name];
	}

	/**
	 * 获取当前Action名称
	 * @access protected
	 */
	protected function getActionName(){
		if(empty($this->name)){
			// 获取Action名称
			$this->name = substr(get_class($this), 0, -6);
		}

		return $this->name;
	}

	/**
	 * 是否AJAX请求
	 * @access protected
	 * @return bool
	 */
	protected function isAjax(){
		return $this->dispatcher->extension_name === 'html';
	}

	/**
	 * 模板显示 调用内置的模板引擎显示方法，
	 * @access protected
	 *
	 * @param string $templateFile 指定要调用的模板文件
	 *
	 * @return null
	 */
	protected function display($templateFile = ''){
		return $this->dispatcher->display($templateFile, $this->tVar);
	}


	/**
	 * 模板变量赋值
	 * @access protected
	 *
	 * @param mixed $name  要显示的模板变量
	 * @param mixed $value 变量的值
	 */
	protected function assign($name, $value = ''){
		if(is_array($name)){
			$this->tVar = array_merge($this->tVar, $name);
		} else{
			$this->tVar[$name] = $value;
		}
	}

	/**
	 * 取得模板显示变量的值
	 * @access protected
	 *
	 * @param string $name 模板显示变量
	 *
	 * @return mixed
	 */
	public function getVar($name = ''){
		return $this->view->get($name);
	}

	/**
	 * 操作错误跳转的快捷方法
	 * @access protected
	 *
	 * @param int    $code        错误码
	 * @param string $extra     额外信息
	 * @param string $jumpUrl     页面跳转地址
	 * @param int    $jumptimeout 跳转等待时间
	 *
	 * @return null
	 */
	protected function error($code, $extra = '', $jumpUrl = '', $jumptimeout = 5){
		$e                      = new Error($code);
		$this->tVar['message']  = $e->getMessage();
		$this->tVar['redirect'] = $e->getUrl();
		$this->tVar['jumpurl']  = $jumpUrl? $jumpUrl : '';
		$this->tVar['timeout']  = $jumptimeout;
		$this->tVar['extra']  = $extra;
		$this->tVar['code']     = $e->getCode();
		$this->tVar['name']     = $e->getName();
		$this->tVar['info']     = $e->getInfo();
		$this->tVar['where']    = $e->getWhere();
		$this->display('!user_error');
		return null;
	}

	/**
	 * 操作成功跳转的快捷方法
	 * @access protected
	 *
	 * @param string $message     提示信息
	 * @param string $jumpUrl     页面跳转地址
	 * @param int    $jumptimeout 跳转等待时间
	 *
	 * @return null
	 */
	protected function success($message, $jumpUrl = '', $jumptimeout = 2){
		$this->tVar['message']  = $message;
		$this->tVar['jumpurl']  = $jumpUrl? $jumpUrl : '';
		$this->tVar['timeout']  = $jumptimeout;
		$this->tVar['code']     = 0;
		$this->display('!success');
		return null;
	}
}
