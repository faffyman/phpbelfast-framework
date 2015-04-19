<?php
namespace PhpBelfast\Controllers;

use Neoxygen\NeoClient\ClientBuilder;

use Slim\Slim;
/**
 * @class GraphDBController
 * @package PhpBelfast\Controllers
 */

class GraphDBController extends BaseController {


    /**
     * @var neo  ClientBuilder
     */
    protected $neo;

    protected $neo_version;

    /**
     * initiate the NeoClient
     */
    public function __construct()
    {
        $this->neo =  ClientBuilder::create()
                            ->addConnection('default','http','10.0.2.2',7474,false)
                            ->build();

        $this->neo_version = $this->neo->getNeoClientVersion();

    }


    /**
     * list the current DB status
     */
    public function index() {

    }


}