<?php
class CAntObjectCond
{
	var $blogic;
	var $fieldName;
	var $operator;
	var $value;

	function __construct($blogic=null, $fieldName, $operator, $value)
	{
		$this->fieldName = $fieldName;
		$this->operator = $operator;
		$this->value = $value;
		$this->blogic = $blogic;
	}
}
