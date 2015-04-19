<?php
namespace PhpBelfast\Controllers;

use Neoxygen\NeoClient\ClientBuilder;

use Faker;

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

    protected $faker;

    /**
     * initiate the NeoClient
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->neo =  ClientBuilder::create()
                            ->addConnection('default','http','10.0.2.2',7474,true,'neo4j','')
                            ->build();

        $this->neo_version = $this->neo->getNeoClientVersion();

        $this->faker =  Faker\Factory::create();

    }


    /**
     * list the current DB status
     */
    public function index() {
        $query = "MATCH (a)-[r]->(b)
                    WHERE labels(a) <> [] AND labels(b) <> []
                    RETURN DISTINCT head(labels(a)) AS This, type(r) as To, head(labels(b)) AS That
                    LIMIT 10";
        $r = $this->neo->sendCypherQuery($query);


        $results = $r->getBody();

        $this->view->set('results', $results['results'][0]);
        $this->app->render('neo4j/status.twig');
    }


    /**
     * create some places
     * City, Country, County
     */
    public function places() {

    }


    /**
     * create some random people
     */
    public function people($nPeople = 25) {

        $aPersonType = ['Hero','Villain','Civilian','Sidekick'];

        foreach (range(1, $nPeople) as $idx) {
            $person = new \stdClass();

            $person->type = $this->faker->randomElement($aPersonType);
            $person->name = $this->faker->name();
            if( $person->type == 'Hero' || $person->type == 'Villain' ) {
                $person->realname = $this->faker->name();
                $person->catchphrase = $this->faker->catchphrase();
            }
            $person->date_of_birth = $this->faker->dateTimeBetween('1968-01-01','1999-01-01');

            $query = 'CREATE (person'.$idx.':Person { name: "'.$person->name.'", type: "'.$person->type.'", dob: "'.$person->date_of_birth->format('d/m/Y').'" '. ( in_array($person->type,array('Hero','Villain') ) ?
                        ',real_name: "'. ($person->realname).'",
                         catchphrase: "'.$person->catchphrase.'"' : '' ) .'})
            RETURN person'.$idx .'';

//            echo $query.'<br />';

            $result = $this->neo->sendCypherQuery($query);

        }





    }


    /**
     * add people to places with one of the following relationships
     * [BORN_IN, LIVED_IN, LIVES_IN, WORKS_IN, WORKED_IN, VISITED]
     */
    public function peopletoplaces() {

    }


    /**
     * relate people to [Place, people]
     */
    public function relatePeople() {

    }


    /**
     * Find a person and list some information about them.
     * @param $name name of person to find.
     */
    public function person($name) {

    }





}