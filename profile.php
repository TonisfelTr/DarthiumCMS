<?php

use Builder\Controllers\BuildManager;

$profileMain = BuildManager::includeFromTemplateAndCompile("profile/main.html", true);

echo $profileMain;

exit(1);

?>