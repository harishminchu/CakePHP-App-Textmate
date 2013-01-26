<?php

/**
 * 
 * Jquery Helper 
 * 
 * @author Christopher Natan
 */
 
class JqueryHelper extends AppHelper {
    
    var $helpers = array("Js"=>array("Jquery"));
/**
 * Html tags used by this helper.
 *
 * @var array
 */
    var $tags = array(  "javascriptblock" => "<script type='text/javascript'>%s</script>",
                        "javascriptlink" => "<script type='text/javascript' src='%s?1'></script>",
                        "rulesmessage" => "validateHelper.ready(%s);"
                     );
    
     
/**
 * Strtotime Compatible Time Expression for lifetime of cached JS files
 *
 * @var string
 */
    
    var $__cacheTime = '-1 days';
	var $__ext = ".js";

	var $__files = array();

	var $__scriptBlock = "";

	var $__rewrite = false;
    
    var $__options = array();    
    var $__includedScript = array("jquery.validate", "jquery.validatehelper");
/**
 * Construct the helper and include JSMin.
 *
 * @return void
 */
	function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		if (!App::import("Vendor", 'jsmin' . DS . 'jsmin')) {
			trigger_error('Could not locate JSMin. Please place it in app/Vendor/JSMin/JSMin.php', E_USER_WARNING);
			return;
		}
	}

/**
 * Similar to Javascript Helper's link function except the file must be local to the application.
 * The JS file is Minified and cached if desired.  Any files that are linked to with inline = false
 * Will be appended to a single file and included in the scripts for layout.
 *
 * @param string/array $file The file(s) to Minify and return
 * @param string/array $scriptBlock  Whether to include $scriptBlock JS function
 * @return mixed string Js
 */
	function link($javaScriptFiles = array(), $scriptBlock = array(), $options = array()) {
		
		$this->__files = $javaScriptFiles;
        $scriptBlock = array_filter($scriptBlock);
        $this->__scriptBlock = implode(";", $scriptBlock);
		
        $this->__options( $options );
        if (isset($this->params['url']['rewrite'])) {
			$this->__rewrite = true;
		}
        
		$hashedFile = $this->__hashFiles($javaScriptFiles);

		$isCacheExist = $this->_checkExistingJsCache($hashedFile);
		if (!$isCacheExist || $this->__rewrite == true) {
			foreach ($this->__files as $file) {
				$pathAndFile = JS . $file . $this->__ext;
				$content[] = $this->readFileContent($pathAndFile);
			}
			$contents = implode("", $content);
            $this->__writeToCacheFile($hashedFile . $this->__ext, $contents);
            return $this->_linkToJsCache($hashedFile);
		}
		return $isCacheExist;
	}

	function __minify($data) {
		$contentData = JSMin::minify($data);
		return $contentData;
	}
    
    function __options( $options ) {
        
        $this->__options = $options;
        if( isset($options["rewrite"]) && $options["rewrite"] == true) {
            $this->__rewrite = true;
        }
        if( isset($options["validate"])) {
            $this->_validate();
        }  
    }
    
	function readFileContent($pathAndFile) {
		$contents = '';
		if (fileExistsInPath($pathAndFile)) {
			$contents = file_get_contents($pathAndFile);
		}
		return $this->__minify($contents);
	}
    
	function __writeToCacheFile($cacheFile, $content) {
		$scriptBlock = $this->Js->domReady($this->__scriptBlock);
		$mergeAll = "/* " . implode(", ", $this->__files) . " " . $this->__scriptBlock . " */ \n" . $content . $scriptBlock;
        /* clearCache(r(WWW_ROOT, '', JS). $cacheFile, "views", $this->__ext); */
        cache(str_replace(WWW_ROOT, '', JS) . $cacheFile, $mergeAll, $this->__cacheTime, 'public');
	}
    
    function __extractScriptBlock() {
        $scriptHash = md5($this->__scriptBlock);
        $reverse = strrev ($scriptHash);
        $length = substr($reverse,0, 7);
        return $length;     
    }

	function __hashFiles($files) {
		$additionalHash = $this->__extractScriptBlock();
        $nameToHashes = "";

		foreach ($files as $file) {
			$separate = explode(".", $file);
			$count = count($separate);
			if ($count >= 2) {
				$nameToHashes .= $separate[1];
			} else {
				$nameToHashes .= $separate[0];
			}
		}
		$hash = "_" . md5($nameToHashes . $additionalHash);
		return $hash;
	}

	function _checkExistingJsCache($cacheFile) {
		$pathAndFile = JS . $cacheFile . $this->__ext;
		if (fileExistsInPath($pathAndFile)) {
			return $this->_linkToJsCache($cacheFile);
		}
		return false;
	}

	function _linkToJsCache($cacheFile) {
		$out = sprintf($this->tags['javascriptlink'], $this->webroot(JS_URL . $cacheFile . $this->__ext));
		return $out;
	}
    
    function _validate() {
         
         $param = $this->__validateParams();
         $this->__arrangeJsNamesIntoOrder();
         
         $model = & ClassRegistry::init($param["model"]);
         if(isset($model->validate)) {
            $jsonObject = $this->Js->object($model->validate); 
         }
        
         $paramForm = $this->enclose($param["formclass"]);
         $paramModel = $this->enclose($param["model"]);
         $paramSuccessMessage = $this->enclose($param["successmessage"]);
         $scriptParams = "$paramForm, $paramModel, $jsonObject, $paramSuccessMessage";
        
         $scriptBlock = sprintf($this->tags['rulesmessage'], $scriptParams);
         $this->__scriptBlock = $scriptBlock . $this->__scriptBlock; 
        
    }
    
    function __arrangeJsNamesIntoOrder() {
        $includedScript = $this->__includedScript;
        $notJqueryFile = true;
        foreach($this->__files as $file) {
            if(strstr($file, "jquery") == false && $notJqueryFile == true) {
                $notJqueryFile = false;
                foreach($includedScript as $included) {
                    $files[] = $included;     
                }    
            }
            $files[] = $file; 
        }
        $this->__files = $files; 
    }
    
    function __validateParams() {
         $params = $this->__options["validate"];
         
         $param["target"] = (isset($params["target"]) ? $params["target"] : null);
         if(!isset($params["formclass"])) {
            $param["formclass"] = null;
         }else{
            $param["formclass"] = $params["formclass"];
         }
         if(!isset($params["model"])) {
            $param["model"] = null;
         }else {
            $param["model"] = $params["model"];
         }
         
         $param["successmessage"] = (isset($params["successmessage"]) ? $params["successmessage"] : null);
         return $param;
    }
    
    function enclose($string, $enclosure = "doubleqoute") {
        $tags = array( "doubleqoute" => '"%s"', "singleqoute" => "'%s'");
        $enclosedString = sprintf($tags[$enclosure], $string);
        return $enclosedString;
    }
}
?>