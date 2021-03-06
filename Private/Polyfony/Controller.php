<?php

namespace Polyfony;

class Controller {


	// method to override
	public function before() {
		
	}
	
	// method to override
	public function after() {
		
	}

	// method to override
	public function default() {
		// default will throw an exception
		Throw new Exception(
			'This action does not exist', 
			500
		);
	}
	
	// include a view
	final public function view(
		string $view_name, 
		$bundle_or_variables = null, 
		?array $variables = []
	) :void {
		
		// set bundle in which the view is
		$view_bundle = 
			$bundle_or_variables && is_string($bundle_or_variables) ? 
			$bundle_or_variables : Router::getCurrentRoute()->bundle;

		// build the path for that view
		$view_path = Config::absolutizePath(
			"Private/Bundles/{$view_bundle}/Views/{$view_name}.php"
		);

		// import variables for the view
		extract(
			$bundle_or_variables && is_array($bundle_or_variables) ? 
			$bundle_or_variables : $variables
		);

		// if the file does not exist
		if(!file_exists($view_path)) {
			// provide debbuging insights
			Logger::debug('Controller->view()', [
				'view_name'		=>$view_name,
				'view_bundle'	=>$view_bundle
			]);
			// throw an exception
			Throw new Exception(
				"Controller->view() View file does not exist [{$view_name}]", 
				500
			);
		}
		// the file exists
		else {
			// marker (note that the microtime is very far from being an acceptable solution)
			$id_marker = Profiler::setMarker(
				"{$view_bundle}/{$view_name}-".
				substr(microtime(true),-4,6), 'view'
			);
			// simply include it
			require($view_path);
			// marker
			Profiler::releaseMarker($id_marker);
		}
		
	}
	
	// forward to another controller in the same bundle
	final public function forward(
		string $controller, 
		$action = 'index'
	) :void {

		// get the current route as a base
		$route = Router::getCurrentRoute();
		
		// and alter it
		$route->controller	= $controller;
		// prevent action names abuse
		$route->action		= Format::fsSafe($action);
		
		// forward to the new route
		Router::forward($route);
		
	}
	
}	

?>
