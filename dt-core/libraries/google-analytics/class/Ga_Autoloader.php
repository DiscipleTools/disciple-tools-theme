<?php

class Ga_Autoloader {

	/**
	 * Registers clas loader.
	 */
	public static function register() {
		spl_autoload_register( "Ga_Autoloader::loader" );
	}

	/**
	 * Class loader.
	 *
	 * @param $class_name
	 */
	private static function loader( $class_name ) {

		// Core classes
		if ( preg_match( '/_Core/', $class_name ) ) {
			$file_name = GA_PLUGIN_DIR . '/class/core/' . $class_name . '.php';
			if ( file_exists( $file_name ) ) {
				require $file_name;
			}
		}

		// Controllers
		if ( preg_match( '/_Controller/', $class_name ) ) {
			$file_name = GA_PLUGIN_DIR . '/class/controller/' . $class_name . '.php';
			if ( file_exists( $file_name ) ) {
				require $file_name;
			}
		}

		// classes
		$file_name = GA_PLUGIN_DIR . '/class/' . $class_name . '.php';
		if ( file_exists( $file_name ) ) {
			require $file_name;
		}

		// tools
		$file_name_tools = GA_PLUGIN_DIR . '/tools/' . $class_name . '.php';
		if ( file_exists( $file_name_tools ) ) {
			require $file_name_tools;
		}

		// Libs
		if ( preg_match( '/Ga_Lib/', $class_name ) ) {
			$file_name = GA_PLUGIN_DIR . '/lib/' . $class_name . '.php';
			if ( file_exists( $file_name ) ) {
				require $file_name;
			}
		}
	}

}
