<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleJavascript extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function arrayToJsArrayString(Array $array){
		$res = '[';
		
		foreach( $array as $message ){
			$res .= "'$message', ";
		}
		
		return mb_substr($res, 0, mb_strlen($res)-2).']';
	}
	
	
	
	//--|GOOGLE-ANALYTICS----------
	
	public function printGoogleAnalyticsCode($gaId = 'UA-XXXXX-X', $withoutAutoTracking = false){
		$res =
			 '<script type="text/javascript">'
				.'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");'
				.'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));'
			.'</script>'
		;
		
		if( !$withoutAutoTracking ){
			$res .=
				'<script type="text/javascript">'
					.'try{'
						.'var pageTracker = _gat._getTracker("'.$gaId.'");'
						.'pageTracker._trackPageview();'
					.'} catch(err) {}'
				.'</script>'
			;
		}
		
		return $res;
	}
	
	
	
	public function printGoogleAnalyticsPageView($gaId, $page){
		return
			'<script type="text/javascript">'
				.'try{'
					.'var pageTracker = _gat._getTracker("'.$gaId.'");'
					.'pageTracker._trackPageview("'.$page.'");'
				.'} catch(err) {}'
			.'</script>'
		;
	}
	
}

?>