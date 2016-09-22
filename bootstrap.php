<?php

$_path = dirname(__FILE__).DIRECTORY_SEPARATOR;

require_once($_path.'vendor'.DIRECTORY_SEPARATOR.'SplClassLoader.php');

(new SplClassLoader('iPublications\\Financial', $_path.'class'))->register();
(new SplClassLoader('iPublications\\Traits',    $_path.'class'))->register();