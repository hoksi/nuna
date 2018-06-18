<?php
include "../../framework/Nuna-ci3.php";

$app = getNuna();
$app->import('view.welcome_message');
$app->run();