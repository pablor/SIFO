<?php

/**
 * Database debug class. Extends the parent with benchmarking and debug utilities.
 *
 * This is done in a separate class to avoid decreased performance in production environments.
 */
class PhpDebug
{

	private static $instance;
	private static $log;
	private static $info;
	private static $warn;
	private static $error;
	private static $nl;
	private static $header;
	private static $messages;

	/**
	 * Singleton of benchmark class.
	 *
	 * @return object Benchmark
	 */
	public static function getInstance()
	{
		if ( !isset( self::$instance ) )
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		self::$log = 1;
		self::$info = 2;
		self::$warn = 3;
		self::$error = 4;

		self::$nl = "\r\n";

		self::$header = '<script type="text/javascript">' . self::$nl;

		/// this is for IE and other browsers w/o console
		self::$header .= 'if (!window.console) console = {};' . self::$nl;
		self::$header .= 'console.log = console.log || function(){};' . self::$nl;
		self::$header .= 'console.warn = console.warn || function(){};' . self::$nl;
		self::$header .= 'console.error = console.error || function(){};' . self::$nl;
		self::$header .= 'console.info = console.info || function(){};' . self::$nl;
		self::$header .= 'console.debug = console.debug || function(){};' . self::$nl;
		self::$header .= '</script>' . self::$nl;
		/// end of IE

		self::$message = array( );
	}

	public function log( $name, $var = null, $type = self::$log )
	{
		$messages_iteration = count( self::$messages );

		self::$messages[$messages_iteration] = '<script type="text/javascript">' . self::$nl;

		switch ( $type )
		{
			case self::$log:
				self::$messages[$messages_iteration] .= 'console.log("' . $name . '");' . self::$nl;
				break;
			case self::$info:
				self::$messages[$messages_iteration] .= 'console.info("' . $name . '");' . self::$nl;
				break;
			case self::$warn:
				self::$messages[$messages_iteration] .= 'console.warn("' . $name . '");' . self::$nl;
				break;
			case self::$error:
				self::$messages[$messages_iteration] .= 'console.error("' . $name . '");' . self::$nl;
				break;
		}

		if ( !empty( $var ) )
		{
			if ( is_object( $var ) || is_array( $var ) )
			{
				$object = json_encode( $var );
				self::$messages[$messages_iteration] .= 'var object' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ' = \'' . str_replace( "'", "\'", $object ) . '\';' . self::$nl;
				self::$messages[$messages_iteration] .= 'var val' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ' = eval("(" + object' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ' + ")" );' . self::$nl;
				switch ( $type )
				{
					case self::$log:
						self::$messages[$messages_iteration] .= 'console.debug(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ');' . self::$nl;
						break;
					case self::$info:
						self::$messages[$messages_iteration] .= 'console.info(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ');' . self::$nl;
						break;
					case self::$warn:
						self::$messages[$messages_iteration] .= 'console.warn(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ');' . self::$nl;
						break;
					case self::$error:
						self::$messages[$messages_iteration] .= 'console.error(val' . preg_replace( '~[^A-Z|0-9]~i', "_", $name ) . ');' . self::$nl;
						break;
				}
			}
			else
			{
				switch ( $type )
				{
					case self::$log:
						self::$messages[$messages_iteration] .= 'console.debug("' . str_replace( '"', '\\"', $var ) . '");' . self::$nl;
						break;
					case self::$info:
						self::$messages[$messages_iteration] .= 'console.info("' . str_replace( '"', '\\"', $var ) . '");' . self::$nl;
						break;
					case self::$warn:
						self::$messages[$messages_iteration] .= 'console.warn("' . str_replace( '"', '\\"', $var ) . '");' . self::$nl;
						break;
					case self::$error:
						self::$messages[$messages_iteration] .= 'console.error("' . str_replace( '"', '\\"', $var ) . '");' . self::$nl;
						break;
				}
			}
		}
		self::$messages[$messages_iteration] .= '</script>' . self::$nl;
	}
}

?>