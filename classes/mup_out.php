<?php

class MUOut {
	protected $tag;
	protected $tagclass;
	protected $tagid;
	protected $content;
	
	public function __construct($tid="",$tclass="") {
		if($tid && $tid != "") {
			$this->tagid = $tid;
		}
		if($tclass && $tclass != "") {
			$this->tagclass = $tclass;
		}
	}
	
	public function __toString() {
		return $this->output(false, false);
	}
	
	public function __invoke($input) {
		return $this->append($input);
	}
	
	public function output($echo = false, $reset = true) {
		$output = "<" . $this->tag;
		if($this->tagclass != "") {
			$output .= " class=\"" . $this->tagclass . "\"";
		}
		if($this->tagid != "") {
			$output .= " id=\"" . $this->tagid . "\"";
		}
		$output .= ">\n\t" . $this->content . "\n</" . $this->tag . ">\n";
		if($reset == true) {
			unset($this->tagclass);
			unset($this->tagid);
			unset($this->content);
		}
		if($echo == true) {
			echo $output;
			return -1;
		} else {
			return $output;
		}
	}
	
	public function append($stuff) {
		if($this->content == "") {
			$this->content .= $stuff;
		} else {
			$this->content .= " " . $stuff;
		}
		return -1;
	}
	
	public function appendbr($n=1) {
		$num = intval($n);
		while ($n>0) {
			$this->content .= "<br>\n";
			$n--;
		}
		return -1;
	}
	
	public function prepend($stuff) {
		$this->content = $stuff . " " . $this->content;
		return -1;
	}
	
	public function addclass($tagclass) {
		if($this->tagclass == "") {
			$this->tagclass .= $tagclass;
		} else {
			$this->tagclass .= " " . $tagclass;
		}
		return -1;
	}
	
	public function addid($tagid, $over = false) {
		if($this->tagid == "") {
			$this->tagid = $tagid;
			return -1;
		} else {
			if($over == true) {
				$this->tagid = $tagid;
				return -1;
			} else {
				return -2;
			}
		}
	}	
}

class MUDiv extends MUOut {
	function __construct() {
		$this->tag = "div";
	}
}

class MUHdr extends MUOut {
	function __construct($level) {
		$this->tag = "h" . intval($level);
	}
}

class MULink extends MUOut {
	private $taghref;
	private $target;

	function __construct() {
		$this->tag = "a";
	}

	public function addhref($href) {
		$this->taghref = $href;
	}

	public function addtarget($target) {
		if($target == "blank" || $target == "new") {
			$target = "_blank";
		}
		$this->target = $target;
	}

	public function output($echo = false, $reset = true) {
		$output = "<" . $this->tag;
		if($this->tagclass != "") {
			$output .= " class=\"" . $this->tagclass . "\"";
		}
		if($this->tagid != "") {
			$output .= " id=\"" . $this->tagid . "\"";
		}
		$output .= " href=\"" . $this->taghref . "\"";
		$output .= " target=\"" . $this->target . "\"";
		$output .= ">" . $this->content . "</" . $this->tag . ">";
		if($reset == true) {
			unset($this->tagclass);
			unset($this->tagid);
			unset($this->content);
		}
		if($echo == true) {
			echo $output;
			return -1;
		} else {
			return $output;
		}
	}
}

class MUSpan extends MUOut {
	function __construct() {
		$this->tag = "span";
	}
}

class MUCSS {
	// $selectors[selector][property] = value;
	private $selectors = array();
	
	function __construct() {
		
	}
	
	// Returns -1 on success
	// 		   -2 if missing selector
	// 		   -3 if missing property
	// 		   -4 if missing value
	public function AddProperty($selector, $property, $value) {
		if(!$selector || $selector == "") {
			return -2;
		}
		if(!$property || $property == "") {
			return -3;
		}
		if(!$value || $value == "") {
			return -4;
		}
		$this->selectors[$selector][$property] = $value;
		return -1;
	}
	
	public function RemoveProperty($selector, $property) {
		if(!$selector || $selector == "") {
			return -2;
		}
		if(!$property || $property == "") {
			return -3;
		}
		unset($this->selectors[$selector][$property]);
		return -1;
	}
	
	public function GetCSS($echo = false, $reset = true) {
		$output = "<style type=\"text/css\">";
		foreach($this->selectors as $selector=>$prop) {
			$output .= "\n\t$selector {";
			foreach($prop as $property=>$value) {
					$output .= "\n\t\t$property: $value;";
			}
			$output .= "\n\t}";
		}
		$output .= "\n</style>";
		if($reset == true) {
			unset($this->selectors);
		}
		if($echo == true) {
			echo $output;
			return -1;
		} else {
			return $output;
		}
	}
}

class MUSheet {

	private $url;

	function __construct($theurl) {
		$this->url = $theurl;	
	}
	
	public function GetLink($echo=false, $reset=true) {
		$output = "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->url . "\">\n";
		if($reset == true) {
			unset($this->url);
		}
		if($echo == true) {
			echo $output;
			return -1;
		} else {
			return $output;
		}
	}
}

class MUMaster {

	private $fullout;
	
	function __construct() {
		$this->fullout = "";
	}
	
	public function __toString() {
		return $this->output(false, false);
	}
	
	public function __invoke($stuff) {
		return $this->append($stuff);
	}
	
	public function append($stuff) {
		$this->fullout .= $stuff;
		return -1;
	}
	
	public function output($echo=false, $reset=true) {
		$output = $this->fullout;
		if($reset == true) {
			unset($this->fullout);
		}
		if($echo == true) {
			echo $output;
			return -1;
		} else {
			return $output;
		}
	}

	public function nonce($tag,$tagcontent,$tagid="",$tagclass="") {
		$output = "<$tag";
		if($tagid != "") {
			$output .= " id=\"$tagid\"";
		}
		if($tagclass != "") {
			$output .= " class=\"$tagclass\"";
		}
		$output .= ">$tagcontent</$tag>";
		return $output;
	}

	public function noncelink($tagcontent="", $taghref="#", $tagtarget="_blank", $tagid="", $tagclass="") {
		$output = "<a href=\"$taghref\" target=\"$tagtarget\"";
		if($tagid != "") {
			$output .= " id=\"$tagid\"";
		}
		if($tagclass != "") {
			$output .= " class=\"$tagclass\"";
		}
		$output .= ">$tagcontent</a>";
		return $output;
	}
	
	public function comment($tagcontent) {
		return "\n<!-- $tagcontent -->\n";
	}
}

?>