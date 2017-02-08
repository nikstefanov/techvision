<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_xml.php');

class AttributeSpecification{
	private $Name;
	private $SpecificationID;

	public function __construct($sid=null, $name=null){
		$this->SpecificationID = $sid;
		$this->Name = $name;
	}

	public function getName(){
		return $this->Name;
	}

	public function getSpecificationID(){
		return $this->SpecificationID;
	}
}

class AttributesPerProduct {
	
	static protected $attribute_specifications_filename = "./xml/_attribute2.xml";
	static protected $attribute_specifications_array = null;

	static protected function load_attribute_specifications(){
		if(is_null(self::$attribute_specifications_array)){
			$attribute_specifications_xml = load_xml(self::$attribute_specifications_filename);
			if(!$attribute_specifications_xml)return;

			self::$attribute_specifications_array = array();
			foreach($attribute_specifications_xml->Attribute as $AttributeSpecification){
				self::$attribute_specifications_array[(int)$AttributeSpecification->SpecificationAttributeOptionID] =
					new AttributeSpecification((int)$AttributeSpecification->SpecificationAttributeID,(string)$AttributeSpecification->Name);
			}
		//print_r(self::$attribute_specifications_array,false);
		}
	}

	static public function load_attribute_specifications_public($url){
		self::$attribute_specifications_filename = $url;
		if(is_null(self::$attribute_specifications_array)){
			$attribute_specifications_xml = load_xml($url);
			if(!$attribute_specifications_xml){return false;}

			self::$attribute_specifications_array = array();
			foreach($attribute_specifications_xml->Attribute as $AttributeSpecification){
				self::$attribute_specifications_array[(int)$AttributeSpecification->SpecificationAttributeOptionID] =
					new AttributeSpecification((int)$AttributeSpecification->SpecificationAttributeID,(string)$AttributeSpecification->Name);
			}
		//print_r(self::$attribute_specifications_array,false);
		}
		return true;
	}

	protected $ProductID;
	protected $Options;

	function __construct($pid=0,$option=null) {
		self::load_attribute_specifications();
		$this->ProductID = intval($pid);
		$this->Options = array();
		//if(!is_null($option)) {$this->Options[] = self::$attribute_specifications_array[$option];}
		if(!is_null($option)) {$this->Options[self::$attribute_specifications_array[$option]->getSpecificationID()] =
			self::$attribute_specifications_array[$option]->getName();}
	}

	public function getProductID() {
		return $this->ProductID;
	}

	public function addOption($option) {
		//if(!in_array($option, $this->Options)){$this->Options[] = self::$attribute_specifications_array[$option];}
		if(!in_array($option, $this->Options)){$this->Options[self::$attribute_specifications_array[$option]->getSpecificationID()] =
			self::$attribute_specifications_array[$option]->getName();}
	}

	public function getOptions() {
		return $this->Options;
	}

	public function isProductID($pid=0) {
		if($this->ProductID == $pid) {return true;}
		else {return false;}
	}

	public function printOptions() {
		if(count($this->Options)>0) {print_r($this->Options,false);}
		else {echo "Empty".PHP_EOL;}
	}
}

?>
