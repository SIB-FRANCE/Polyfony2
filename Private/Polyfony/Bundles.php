<?php
/**
 * PHP Version 5
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;
 
class Bundles {

	protected static $_bundles		= array();
	protected static $_routes		= array();
	protected static $_configs		= array();

	// will get the list of bundles and get their routes and runtimes
	public static function init() :void {

		// if cache is enabled and in prod load the cache, else parse bundles
		Config::isProd() && Cache::has('Includes') ? self::loadCachedDependencies() : self::loadDependencies();
		
		// include what has been found
		self::includeLoaders();
		
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
	
	private static function includeLoaders() :void {
		
		// for each route or runtime filavailablee
		foreach(array_merge(self::$_routes, self::$_configs) as $file) {
			// include it
			include($file);	
		}
		
	}
	
	// get assets for a bundle
	public static function getAssets(string $bundle) :array {
		// empty list of assets
		$assets_types = array();
		// set the assets folder
		$assets_folder = "../Private/Bundles/{$bundle}/Assets/";
		// if the assets folder exists
		if(file_exists($assets_folder) && is_dir($assets_folder)) {
			// for each subfolder in the assets folder
			foreach(scandir($assets_folder) as $asset_type) {
				// if it's a folder, and a normal one
				if(substr($asset_type,0,1) != '.' && is_dir($assets_folder.$asset_type)) {	
					// spool it with a trailing slash
					$assets_types[$asset_type] = "{$assets_folder}{$asset_type}/";
				}
			}
		}
		// return the list of available assets folders
		return($assets_types);
		
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
		return(self::$_bundles);
		
	}
	
}

?>
