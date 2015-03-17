<?php

function getDbOptions($isProd)
{
	if($isProd)
		return array(
	        'db' => array(
	            'driver'   => 'pdo_mysql',
	            'dbname'   => 'gm',
	            'host'     => 'the.db.fr',
	            'user'     => 'usr',
	            'password' => 'toto',
	            'charset'  => 'utf8',
	        )
	    );

	else
		return array(
	        'db' => array(
	            'driver'   => 'pdo_mysql',
	            'dbname'   => 'gm',
	            'host'     => '127.0.0.1',
	            'user'     => 'usr',
	            'password' => 'toto',
	            'charset'  => 'utf8',
	        )
	    );  
}