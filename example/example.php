<?php
/**
 * Created by PhpStorm.
 * User: michael1
 * Date: 2015-05-25
 * Time: 9:17 AM
 */

/*
 * Using composer you don't need to do this.
 */
require_once("../src/API.php");
require_once("../src/Endpoint.php");
require_once("../src/Models.php");

use mcarpenter\vinephp\API;
use mcarpenter\vinephp\VineException;

try {
    $api = new API();

    /**
     * @var mcarpenter\vinephp\User $user
     */
    $user = $api->login(array( "username" => "email@domain.com", "password" => "password"));

    /**
     * @var mcarpenter\vinephp\UserCollection $followers
     */
    $followers = $user->followers();

    /**
     * @var mcarpenter\vinephp\PostCollection $timeline
     */
    $timeline = $user->timeline();
} catch (VineException $e) {
    echo $e->getMessage();
}