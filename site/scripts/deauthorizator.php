<?php

include "../../engine/main.php";
\Engine\Engine::LoadEngine();

\Users\UserAgent::SessionDestroy();
header("Location: ../../profile.php");
exit;
