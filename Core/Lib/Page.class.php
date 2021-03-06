<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------

class Page{

	// 分页栏每页显示的页数
	public $rollPage = 7;
	// 页数跳转时要带的参数
	public $parameter;
	// 分页URL地址
	public $url = '';
	// 默认列表每页显示行数
	public $listRows;
	// 起始行数
	public $firstRow;
	// 分页总页面数
	protected $totalPages;
	// 总行数
	protected $totalRows;
	// 当前页数
	protected $nowPage;
	// 分页的栏的总页数
	protected $coolPages;
	// 分页显示定制
	protected $config = array(
		'header' => '条记录',
		'prev'   => '&lt;',
		'next'   => '&gt;',
		'first'  => '[&lt;',
		'last'   => '&gt;]',
		'theme'  => '<ul class="pagination"><li class="disabled" title="%totalRow%%header%"><a>%nowPage%/%totalPage% (~%totalRow%)</a></li>%first%%prePage%%upPage%%linkPage%%downPage%%nextPage%%end%</ul>'
	);
	// 默认分页变量名
	protected $varPage;

	/**
	 * 架构函数
	 * @access public
	 *
	 * @param int    $totalRows  总的记录数
	 * @param int    $listRows   每页显示记录数
	 * @param array  $parameter  分页跳转的参数
	 * @param string $var_page
	 */
	public function __construct($totalRows, $listRows = 20, $parameter = '', $var_page = VAR_PAGE){
		$this->totalRows  = $totalRows;
		$this->parameter  = $parameter;
		$this->varPage    = $var_page;
		$this->listRows   = intval($listRows);
		$this->totalPages = ceil($this->totalRows/$this->listRows); //总页数
		$this->coolPages  = ceil($this->totalPages/$this->rollPage);
		$this->nowPage    = !empty($_GET[$this->varPage])? intval($_GET[$this->varPage]) : 1;
		if($this->nowPage < 1){
			Think::fail_error(ERR_RANGE_PAGE, '页数必须在 1~' . $this->totalPages . ' 之间');
		} elseif($this->nowPage > $this->totalPages){
			$this->nowPage = $this->totalPages + 1;
		}
		$this->firstRow = $this->listRows*($this->nowPage - 1);
	}

	public function setConfig($name, $value){
		if(isset($this->config[$name])){
			$this->config[$name] = $value;
		}
	}

	public function setParam($p){
		$this->parameter = $p;
	}

	public function showArray(){
		$ret              = [];
		$ret['nowPage']   = $this->nowPage;
		$ret['totalPage'] = $this->totalPages;
		$ret['totalRows'] = $this->totalRows;

		// 分析分页参数
		if(!$this->url){
			if($this->parameter && is_string($this->parameter)){
				parse_str($this->parameter, $parameter);
			} elseif(is_array($this->parameter)){
				$parameter = $this->parameter;
			}
			$parameter[$this->varPage] = '__PAGE__';
			$this->url                 = UI(METHOD_NAME, $parameter, true);
		}
		$ret['url'] = $this->url;
		return $ret;
	}

	/**
	 * 分页显示输出
	 * @access public
	 */
	public function show(){
		if(0 == $this->totalRows){
			return '';
		}
		$p           = $this->varPage;
		$nowCoolPage = ceil($this->nowPage/$this->rollPage);

		// 分析分页参数
		if($this->url){
			$url = $this->url . URL_PATHINFO_DEPR . '__PAGE__';
		} else{
			if($this->parameter && is_string($this->parameter)){
				parse_str($this->parameter, $parameter);
			} elseif(is_array($this->parameter)){
				$parameter = $this->parameter;
			}
			$parameter[$p] = '__PAGE__';
			$url           = UI(METHOD_NAME, $parameter, true);
		}
		//上下翻页字符串
		$upRow   = $this->nowPage - 1;
		$downRow = $this->nowPage + 1;
		if($upRow > 0){
			$upPage = "<li title='上一页'><a href='" . str_replace('__PAGE__', $upRow, $url) . "'>" .
					  $this->config['prev'] . "</a>";
		} else{
			$upPage =
					"<li class='disabled' title='上一页'><a href='javascript:void(0);'>" . $this->config['prev'] . "</a>";
		}
		if($downRow <= $this->totalPages){
			$downPage = "<li title='下一页'><a href='" . str_replace('__PAGE__', $downRow, $url) . "'>" .
						$this->config['next'] . "</a></li>";
		} else{
			$downPage = "<li class='disabled' title='下一页'><a href='javascript:void(0);'>" .
						$this->config['next'] . "</a></li>";
		}

		// << < > >>
		$preRow = $this->nowPage - $this->rollPage;
		if($nowCoolPage == 1){
			$prePage = "<li class='disabled' title='上{$this->rollPage}页'><a href='javascript:void(0);'>&lt;&lt;</a></li>";
		} else{
			$prePage = "<li title='上{$this->rollPage}页'><a href='" .
					   str_replace('__PAGE__', $preRow, $url) . "'>&lt;&lt;</a></li>";
		}
		if($this->nowPage == 1){
			$theFirst = "<li class='disabled' title='第一页'><a href='javascript:void(0);'>" .
						$this->config['first'] . "</a></li>";
		} else{
			$theFirst = "<li title='第一页'><a href='" . str_replace('__PAGE__', 1, $url) . "'>" .
						$this->config['first'] . "</a></li>";
		}

		$nextRow   = $this->nowPage + $this->rollPage;
		$theEndRow = $this->totalPages;
		if($nowCoolPage == 1 && !$this->totalPages == $this->nowPage){
			$nextPage = "<li title='下{$this->rollPage}页'><a href='" .
						str_replace('__PAGE__', $nextRow, $url) . "'>&gt;&gt;</a></li>";
		} else{
			$nextPage = "<li class='disabled' title='下{$this->rollPage}页'><a href='javascript:void(0);'>&gt;&gt;</a></li>";
		}
		if($this->totalPages == $this->nowPage){
			$theEnd = "<li class='disabled' title='最后一页'><a href='javascript:void(0);'>" .
					  $this->config['last'] . "</a></li>";
		} else{
			$theEnd = "<li title='最后一页'><a href='" . str_replace('__PAGE__', $theEndRow, $url) . "'>" .
					  $this->config['last'] . "</a></li>";
		}

		// 1 2 3 4 5
		$linkPage = "";
		$width    = floor($this->rollPage/2);
		$left     = $this->nowPage - $width;
		$right    = $this->nowPage + $width;
		if($left < 1){
			$right = $this->rollPage;
			$left  = 1;
		}
		if($right > $this->totalPages){
			$right = $this->totalPages;
		}
		for($page = $left; $page <= $right; $page++){
			$act = $page == $this->nowPage? ' class="active"' : '';
			$linkPage .= "<li{$act} title='第{$page}页'><a href='" . str_replace('__PAGE__', $page, $url) . "'>" .
						 $page . "</a></li>";
		}
		$pageStr = str_replace(array(
									'%header%',
									'%nowPage%',
									'%totalRow%',
									'%totalPage%',
									'%upPage%',
									'%downPage%',
									'%first%',
									'%prePage%',
									'%linkPage%',
									'%nextPage%',
									'%end%'
							   ),
							   array(
									$this->config['header'],
									$this->nowPage,
									$this->totalRows,
									$this->totalPages,
									$upPage,
									$downPage,
									$theFirst,
									$prePage,
									$linkPage,
									$nextPage,
									$theEnd
							   ),
							   $this->config['theme']
		);
		return $pageStr;
	}

	public function __toString(){
		return $this->show();
	}
}
