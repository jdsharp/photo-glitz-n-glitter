<?php

define('DPWEB_DOC_ROOT', dirname(__FILE__) );
define('DPWEB_LIBRARY', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'library');
define('DPWEB_URL', '/');
define('DPWEB_LIBRARY_INCLUDE',  '/(\.je?pg)$/i');
define('DPWEB_LIBRARY_METADATA', '_metadata.json');
define('DPWEB_FILE_METADATA',    '_metadata.json');
define('DPWEB_CACHE', implode(DIRECTORY_SEPARATOR, array(DPWEB_DOC_ROOT,'htdocs','library')));