<?php

class SFM_ContentGenerator {
	
	public function __construct(&$vars)
	{
		foreach ($vars as $key => &$value) {
			$this->$key = $value;
		}
		
		
	}
	
	public function process($filepath)
	{
    	ob_start();
    	include $filepath;
    	$content = ob_get_contents();
    	ob_end_clean();
    	
    	return $content;
	}
	
	public function template($name=null)
    {    	
    	$filepath = SFM_Template::getInstance()->getTpl($name);
    	echo $this->process($filepath);
    }
}

?>