<?php
require_once('../../framework/Nuna-ci2.php');

$app = new Nuna();

$app->import('tpl.welcome_message.php');

$member = $app->import('model.member');

$email = $app->import('nuna.email');

$app->run(true);