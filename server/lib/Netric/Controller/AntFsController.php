<?php
/**
 * Controller for handling Ant File Server
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Entity\BrowserView\BrowserView;

class AntFsController extends Mvc\AbstractController
{
    /**
     * Save a browser view
     *
     * @param array $params Associative array of request params
     */
    public function upload($params = array())
    {

        print_r($params);
        print_r($_POST);
        print_r($_FILES);
    }
}