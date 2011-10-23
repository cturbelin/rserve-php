<?php
/**
 * Rserve native array wrapper
 * @author Clément Turbelin
 * From Rserve java Client & php Client
 */
 
/**
* php Native array with attributes feature
* results wrapped in this class could be used as an array ($result['toto']) to get a results and using methods to access to attributes
*/
class Rserve_RNative implements ArrayAccess {
    
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
     * @var int (Rserve_Connection XT_* const value)
     */
    private $type = NULL;
    
    /**
     * 
     * @param $data values
     * @param Rserve_RNative $attributes 
     * @param int $exp_type expression type
     */
    public function __construct($data, $attributes = NULL, $exp_type = NULL) {
        $this->data = $data;
        $this->attr = $attributes;
        $this->type = $exp_type;
    }
    
    public function getAttr($name) {
        return (isset($this->attr[$name])) ? $this->attr[$name] : NULL;
    }
    
    public function hasAttr($name) {
        return (isset($this->attr[$name])) ? TRUE : FALSE;
    }
    
    /**
     * Type of the parsed expression (vector, list, etc) (@see Rserve_Parser::xtName())
     */
    public function getType() {
    	return $this->type;
    }
    
    /**
     * Get the attributes
     * @return Rserve_RNative
     */
    public function getAttributes() {
        return $this->attr;
    }
   
    // ArrayAccess Implementation allows array-like syntax for instances
    
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }
    
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    
}