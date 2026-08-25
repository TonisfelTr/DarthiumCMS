<?php

use Builder\Controllers\BuildAgent;

$profileMain = BuildAgent::includeFromTemplateAndCompile("profile/main.html", true);

echo $profileMain;

\Users\Services\FlashSession::clearContainers();

exit(1);

?>