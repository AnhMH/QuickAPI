<?php

namespace Social;

class Twitter {

	public static $_instance = false;

	protected static $_config = null;


	public static function _init() {
		static::$_config = \Config::get('twitter');
	}


	public static function forge( $token = null, $token_secret = null ) {
		if (static::$_instance)
			return static::$_instance;

		static::$_instance = $token ? new TwitterOAuth( static::$_config['key'], static::$_config['secret'], $token, $token_secret ) 
									: new TwitterOAuth( static::$_config['key'], static::$_config['secret'] );

		return static::$_instance;

	}


	public static function __callStatic( $method, $arguments ) {
		
		if( !method_exists( static::forge(), $method ) )
			throw new Exception( 'No such method (' . $method . ')' );
		
		return call_user_func_array( array( static::forge(), $method ), $arguments );
		
	}


}