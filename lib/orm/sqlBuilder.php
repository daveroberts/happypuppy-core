<?php

namespace HappyPuppy;
class SQLBuilder
{
	private $_select;
	private $_from;
	private $_left_joins;
	private $_and_where;
	private $_or_where;
	private $_order_by;
	private $_sort_order;
	public function __construct()
	{
		$this->_select = '*';
		$this->_from = '';
		$this->_left_joins = array();
		$this->_and_where = array();
		$this->_or_where = array();
		$this->_order_by = '';
		$this->_sort_order = '';
	}
	public function select($select)
	{
		$this->_select = $select;
		return $this;
	}
	public function from($from)
	{
		$this->_from = $from;
		return $this;
	}
	public function leftJoin($table, $condition)
	{
		$_left_joins[] = array($table, $condition);
		return $this;
	}
	public function where($where)
	{
		$this->_and_where[] = $where;
		return $this;
	}
	public function or_where($where)
	{
		$this->_or_where[] = $where;
		return $this;
	}
	public function orderBy($order_by, $sort_order = 'desc')
	{
		$this->_order_by = $order_by;
		$this->_sort_order = $sort_order;
		return $this;
	}
	public function toString()
	{
		$sql = "SELECT ";
		$sql .= $this->_select;
		$sql .= " FROM ";
		$sql .= $this->_from;
		foreach($this->_left_joins as $left_join)
		{
			$sql .= " LEFT JOIN ".$left_join[0]." ON ".$left_join[1];
		}
		if (	count($this->_and_where) != 0 ||
				count($this->_or_where) != 0)
		{
			$where_clause = '';
			foreach($this->_and_where as $where)
			{
				if (strcmp($where_clause, '') == 0)
				{
					$where_clause = $where;
				}
				else
				{
					$where_clause .= " AND ".$where;
				}
			}
			foreach($this->_or_where as $where)
			{
				if (strcmp($where_clause, '') == 0)
				{
					$where_clause = $where;
				}
				else
				{
					$where_clause .= " OR ".$where;
				}
			}
			$sql .= " WHERE ";
			$sql .= $where_clause;
		}
		if (strcmp($this->_order_by, '') != 0)
		{
			$sql .= " ORDER BY ";
			$sql .= $this->_order_by;
			$sql .= " ".$this->sort_order;
		}
		return $sql;
	}
}