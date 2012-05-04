<?php

/**
 * Sifo debug class.
 */
class SifoDebug
{
	public static function log( $message, $type = 'log' )
	{
		if ( Domains::getInstance()->getDevMode() )
		{
			switch ( $type )
			{
				case 'log':
				case 'info':
				case 'warn':
				case 'error':
				{
					$is_object = false;
					if ( is_object( $message ) || is_array( $message ) )
					{
						$is_object = true;

						$message = "'" . str_replace( "'", "\\'", json_encode( $message ) ) . "'";
					}
					else
					{
						$message = "'" . str_replace( "'", "\\'", $message ) . "'";
					}
					Registry::push( 'debug_messages', array( 'type' => $type, 'is_object' => $is_object, 'message' => $message ) );
				}break;
				default:
				{
					trigger_error( 'undefined debug type => ' . $type . ' for debug message: ' . $message );
				}break;
			}
		}
	}

}
?>