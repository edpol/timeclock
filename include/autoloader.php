<?php
/**
 * Simple autoloader
 *
 * @param $class_name - String name for the class that is trying to be loaded.
 */
function my_custom_autoloader( $class_name ) : void
{
    $file = LIB_PATH."/".$class_name.'.php';

    if ( file_exists($file) ) {
        require_once $file;
    }
}

// add a new autoloader by passing a callable into spl_autoload_register()
echo 'defined ';
spl_autoload_register( 'my_custom_autoloader' );
echo "autoloader<br>";


/*
spl_autoload_register(
    function($className) {
        $namespace=str_replace("\\","/",__NAMESPACE__);
        $className=str_replace("\\","/",$className);
        $class=LIB_PATH."/classes/".(empty($namespace)?"":$namespace."/")."{$className}.class.php";
        include_once($class);
    }
);
*/
