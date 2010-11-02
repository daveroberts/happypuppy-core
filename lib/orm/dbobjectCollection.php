<?php

// dbobjectCollection returns false for is_array and true for is_object
// use this to check for "array like" behavior
function like_array(&$x)
{
    return (bool)($x instanceof ArrayAccess or is_array($x));
}

class dbobjectCollection implements Iterator, ArrayAccess, Countable
{
	private $arr;
	function __construct($arr = null)
	{
		if ($arr == null)
		{
			$this->arr = array();
		}
		else
		{
			$this->arr = $arr;
		}
	}
	public function rewind()
	{
		return reset($this->arr);
    }
    public function current()
    {
        return current($this->arr);
    }
    public function key()
    {
        return key($this->arr);
    }
    public function next()
    {
		return next($this->arr);
    }
	public function valid()
    {
		return key($this->arr) !== null;
    }
 	public function offsetSet($offset, $value)
 	{
 		if ($offset == "")
 		{
 			array_push($this->arr, $value);
 		}
 		else
 		{
	        $this->arr[$offset] = $value;
 		}
    }
    public function offsetExists($offset)
    {
        return isset($this->arr[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->arr[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->arr[$offset]) ? $this->arr[$offset] : null;
    }
    public function count()
    {
    	return count($this->arr);
    }
	function __toString()
	{
		return print_r($this->arr, true);
	}
}
?>