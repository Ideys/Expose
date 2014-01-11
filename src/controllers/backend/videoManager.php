<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$videoManagerController = $app['controllers_factory'];

$videoManagerController->assert('_locale', implode('|', $app['languages']));

return $videoManagerController;
