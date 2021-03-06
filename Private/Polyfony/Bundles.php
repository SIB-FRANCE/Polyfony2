<?php

namespace Polyfony;
 
class Bundles {

	protected static array $_bundles	= [];
	protected static array $_routes		= [];
	protected static array $_configs	= [];

	// will get the list of bundles and get their routes and runtimes
	public static function init() :void {

		// marker
		Profiler::setMarker('Bundles.init', 'framework');

		// if cache is enabled and in prod load the cache, else parse bundles
		Config::isProd() && Cache::has('Includes') ? self::loadCachedDependencies() : self::loadDependencies();
		
		// now that we have the list of config files, pass them to the config class
		Config::includeBundlesConfigs(self::$_configs);

		// now that we have the list of route files, pass them to the router class
		Router::includeBundlesRoutes(self::$_routes);

		// marker
		Profiler::releaseMarker('Bundles.init');

	}
	
	private static function loadCachedDependencies() :void {
	
		// get from the cache
		$cache = Cache::get('Includes');
		// put everything at its rightful place
		self::$_bundles		= $cache['bundles'];
		self::$_routes		= $cache['routes'];
		self::$_configs		= $cache['configs'];
		
	}
	
	private static function loadDependencies() :void {
	
		// for each available bundle
		foreach(scandir('../Private/Bundles/') as $bundle) {
			// if it's an actual file
			if(substr($bundle,0,1) != '.') {
				// remember the bundle name
				self::$_bundles[] = $bundle;
				// route file
				$bundle_routes = "../Private/Bundles/{$bundle}/Loader/Route.php";
				// runtime file
				$bundle_config = "../Private/Bundles/{$bundle}/Loader/Config.php";
				// if a route file exists
				!file_exists($bundle_routes) ?: self::$_routes[] = $bundle_routes;
				// if a runtime file exists
				!file_exists($bundle_config) ?: self::$_configs[] = $bundle_config;
			}
		}
		// save in the cache (overwrite)
		Cache::put('Includes', array(
			'routes'	=>self::$_routes,
			'configs'	=>self::$_configs,
			'bundles'	=>self::$_bundles
		), true);
		
	}
	
	// get locales for a bundle
	public static function getLocales(string $bundle) :array {

		// declare an array to hold the list
		$locales = array();
		// set the locales path
		$locales_path = "../Private/Bundles/{$bundle}/Locales/";
		// if the directory exists
		if(file_exists($locales_path) && is_dir($locales_path)) {
			// for each file in the directory
			foreach(scandir($locales_path) as $locales_file) {
				// if the file is a normal one
				if(substr($locales_file,0,1) != '.' ) {
					// push it into the array of locales
					$locales[] = $locales_path . $locales_file ;
				}
			}
		}
		// return all found locales
		return($locales);
		
	}
	
	// get the list of available bundles
	public static function getAvailable() :array {
		
		// return the current list
		return self::$_bundles;
		
	}

}

?>
