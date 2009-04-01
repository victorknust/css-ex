<?php

class Parser
{
	
	private $_css_content;
	private $_parsed_content;
	private $_reserved_tokens;
	
	function __construct($path)
	{
		$this->_reserved_tokens = array(
		  '$imagereplace',
		  '$roundedcorners',
		);
		$safe_path = urlencode($path);
		try {
			$this->_css_content = $this->readFile($path);
		}
		catch(Exception $e)
		{
			header("HTTP/1.0 404 Not Found"); 
			exit();
		}
	}
	
	private function readFile($path)
	{
	  $contents = @file($path);
      if($contents == NULL)
      {
         throw new Exception('CSS file does not exist');
      }
      else {
      	return $contents;
      }
	}
	
	private function replaceTokens($variable, $value)
	{
		if(!in_array($variable, $this->_reserved_tokens)) {
	       $this->_parsed_content = str_replace($variable, 
	                                            $value, 
	                                            $this->_parsed_content);
		}
	}
	
	private function processFunction($function_name, $arg, $haystack)
	{
		$toreplace = $arg[0];
		if(function_exists($function_name))
		{
    		return str_replace($toreplace, 
                               $function_name($arg[1]),
                               $haystack);
		}
		else 
		{
			return $haystack;
		}
	}
	
	public function parse()
	{
		header('content-type:text/css');
		
		$content = '';
		$variables = array();
		$functions = array();

		// parse variables and functions from css
		foreach($this->_css_content as $line)
		{
			preg_match('/(\$[a-zA-Z\-\_0-9]+)\:([^;]+);/', $line, $vmatches);
			preg_match('/(\$[a-zA-Z\-\_0-9]+)\([\"\']?([^;\'\"]+)[\"\']?\);/', 
			           $line, $fmatches);
			$newline = $this->processFunction(str_replace('$', '', $fmatches[1]), 
			                                  array($fmatches[0], $fmatches[2]), 
			                                  $line);
			$variables[$vmatches[1]] = $vmatches[2];
			$this->_parsed_content .= $newline;
		}
		
//		array_map(array("Parser", "processFunction"), array_keys($functions), $functions);
		// remove variable definitions
		$this->_parsed_content = preg_replace('/\$[a-zA-Z\-\_0-9]+\:[^;]+;\n/', '', $this->_parsed_content);
		// replace variables with parsed values
		array_map(array("Parser", "replaceTokens"), array_keys($variables), $variables);
	}
	
	
	function printCSS()
	{
		header("HTTP/1.0 200 OK");
        header('content-type:text/css');
        print $this->_parsed_content;
	}
	
}

include('css_functions.php');

$p = new Parser($_GET['path']);
$p->parse();
$p->printCSS();






?>