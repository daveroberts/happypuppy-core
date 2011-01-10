<?php

namespace HappyPuppy;
class Pagination
{
	public $prefix;
	private $total_Pages = 0;
	public function __construct($prefix)
	{
		$this->prefix = $prefix;
	}
	public function isSortColSet(){ return (isset($_REQUEST[$this->prefix."sort_col"]) && !empty($_REQUEST[$this->prefix."sort_col"])); }
	public function sortCol(){ return $_REQUEST[$this->prefix."sort_col"]; }
	public function isSortDirSet(){ return (isset($_REQUEST[$this->prefix."sort_dir"]) && !empty($_REQUEST[$this->prefix."sort_dir"])); }
	public function sortDir(){ return $_REQUEST[$this->prefix."sort_dir"]; }
	public function searchName(){ return $this->prefix."search"; }
	public function isSearchSet(){ return (isset($_REQUEST[$this->prefix."search"]) && !empty($_REQUEST[$this->prefix."search"])); }
	public function search(){ return $_REQUEST[$this->prefix."search"]; }
	public function isPageSet(){ return (isset($_REQUEST[$this->prefix."page"]) && !empty($_REQUEST[$this->prefix."page"])); }
	public function page(){
		if (array_key_exists($this->prefix."page", $_REQUEST) && !empty($_REQUEST[$this->prefix."page"])){
			return $_REQUEST[$this->prefix."page"];
		} else {
			return 1;
		}
	}
	public function colHeader($html, $col = '')
	{
		if ($col == ''){ $col = strtolower($html); }
		$new_sort_dir = "DESC";
		if ($this->sortCol() == $col && $this->sortDir() == "DESC"){ $new_sort_dir = "ASC"; }
		$new_get = $_GET;
		unset($new_get["url"]);
		$new_get[$this->prefix."sort_dir"] = $new_sort_dir;
		$new_get[$this->prefix."sort_col"] = $col;
		$new_get[$this->prefix."page"] = 1;
		$vars = "?";
		foreach($new_get as $k=>$v){
			$vars .= $k."=".urlencode($v)."&";
		}
		$vars = substr($vars, 0, strlen($vars) - 1);
		return link_to($html, $vars, array("id"=>$this->prefix."_".$col));
	}
	public function currentPage()
	{
		return $this->page();
	}
	public function setTotalPages($total_pages)
	{
		$this->total_Pages = $total_pages;
	}
	public function totalPages()
	{
		return $this->total_Pages;
	}
	public function atFirstPage()
	{
		return $this->currentPage() == 1;
	}
	public function atLastPage()
	{
		return self::CurrentPage() >= $this->totalPages();
	}
	public function previousPage($html = "Previous Page")
	{
		$new_get = $_GET;
		unset($new_get["url"]);
		$new_get[$this->prefix."page"] = ($this->currentPage()-1);
		$vars = "?";
		foreach($new_get as $k=>$v){
			$vars .= $k."=".urlencode($v)."&";
		}
		$vars = substr($vars, 0, strlen($vars) - 1);
		return link_to($html, $vars, array("id"=>$this->prefix."_prev"));
	}
	public function nextPage($html = "Next Page")
	{
		$new_get = $_GET;
		unset($new_get["url"]);
		$new_get[$this->prefix."page"] = ($this->currentPage()+1);
		$vars = "?";
		foreach($new_get as $k=>$v){
			$vars .= $k."=".urlencode($v)."&";
		}
		$vars = substr($vars, 0, strlen($vars) - 1);
		return link_to($html, $vars, array("id"=>$this->prefix."_next"));
	}
}