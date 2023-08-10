<?php

/**
 * Rserve native array wrapper
 * @author Clément Turbelin
 * From Rserve java Client & php Client
 */

namespace Sentiweb\Rserve;

/**
 * php Native array with attributes feature
 * results wrapped in this class could be used as an array ($result['toto']) to get a results and attributes could be accessed using methods
 */
class ArrayWrapper implements \ArrayAccess
{

	/**
	 * @var array data = R values
	 */
	private $data = array();

	/**
	 * @var array R Attributes for this structure
	 */
	private $attr = array();

	/**
	 * Parsed expression type
	 * @var int (Connection::XT_* const value)
	 */
	private $type = null;

	/**
	 *
	 * @param $data values
	 * @param Rserve_RNative $attributes
	 * @param int $exp_type expression type
	 */
	public function __construct($data, $attributes = null, $exp_type = null)
	{
		$this->data = $data;
		$this->attr = $attributes;
		$this->type = $exp_type;
	}

	/**
	 * @param string $name get the attribute named $name
	 * @return mixed
	 */
	public function getAttr($name)
	{
		return isset($this->attr[$name]) ? $this->attr[$name] : null;
	}

	/**
	 * Test if an attibute exists
	 * @param string $name
	 */
	public function hasAttr($name)
	{
		return isset($this->attr[$name]) ? true : false;
	}

	/**
	 * Type of the parsed expression (vector, list, etc) (@see Parser::xtName())
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get the attributes
	 * @return Rserve_RNative
	 */
	public function getAttributes()
	{
		return $this->attr;
	}

	// ArrayAccess Implementation allows array-like syntax for instances

	public function offsetSet($offset, $value): void
	{
		$this->data[$offset] = $value;
	}

	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset): void
	{
		unset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}
}
