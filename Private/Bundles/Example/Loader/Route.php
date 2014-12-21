<?php

// declare the main error route (it doesn't have to have an url)
Polyfony\Router::addRoute('error')
	->destination('Example','Example','error');


Polyfony\Router::addRoute('main-index')
	->url('/')
	->destination('Example','Example','index');

Polyfony\Router::addRoute('dynamic')
	->url('/dynamic/:action/:id/')
	->restrict(array(
		'id'=>true,
		'action'=>array('create','edit','update','delete')
	))
	->destination('Example','Example')
	->trigger('action');

Polyfony\Router::addRoute('test')
	->url('/test/')
	->destination('Example','Example','test');

?>