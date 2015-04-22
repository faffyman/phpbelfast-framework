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
                            ->addConnection('default','http','10.0.2.2',7474,true,'neo4j','demo')
                            ->setAutoFormatResponse(true) // REQUIRED FOR getResult() and getRows() functions !
                            ->build();

        $this->neo_version = $this->neo->getNeoClientVersion();

        $this->faker =  Faker\Factory::create();

    }


    /**
     * list the current DB node types and relationships
     */
    public function index() {

        //What Labels (object types) exist in our system?
        $labels = $this->neo->getLabels()->getBody();
        $this->view->set('labels', $labels);


        // What is related to What?
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
    public function loadplaces() {

        $aPlaces = array(
            'Belfast' => array('type' => 'City', 'capital' => True),
            'Alicante' => array('type' => 'City', 'country' => 'Spain'),
            'Amsterdam' => array('type' => 'City', 'capital' => False, 'country' => 'Netherlands'),
            'Barcelona' => array('type' => 'City', 'country' => 'Spain'),
            'Birmingham' => array('type' => 'City', 'country' => 'England'),
            'Bordeaux' => array('type' => 'City', 'country' => 'France'),
            'Bristol' => array('type' => 'City', 'country' => 'England'),
            'Edinburgh' => array('type' => 'City', 'country' => 'Scotland', 'capital' => 'True'),
            'Faro' => array('type' => 'City', 'country' => 'Portugal'),
            'Geneva' => array('type' => 'City', 'country' => 'Switzerland'),
            'Glasgow' => array('type' => 'City', 'country' => 'Scotland'),
            'Ibiza' => array('type' => 'City', 'country' => 'Spain'),
            'Jersey' => array('type' => 'City', 'country' => 'Channel Islands'),
            'Krakow' => array('type' => 'City', 'country' => 'Poland'),
            'Liverpool' => array('type' => 'City', 'country' => 'England'),
            'London' => array('type' => 'City', 'country' => 'England', 'capital' => 'True'),
            'Majorca' => array('type' => 'City', 'country' => 'Spain'),
            'Malaga' => array('type' => 'City', 'country' => 'Spain'),
            'Malta' => array('type' => 'City', 'country' => 'Maltese Islands'),
            'Manchester' => array('type' => 'City', 'country' => 'England'),
            'Newcastle' => array('type' => 'City', 'country' => 'England'),
            'Nice' => array('type' => 'City', 'country' => 'France'),
            'Paris' => array('type' => 'City', 'country' => 'France', 'capital' => 'True'),
            'Reykjavik' => array('type' => 'City', 'country' => 'Iceland', 'capital' => 'True'),
            'Split' => array('type' => 'City', 'country' => 'Croatia'),
            'Dubrovnik' => array('type' => 'City', 'country' => 'Croatia'),

        );


        foreach($aPlaces as $k=> $aPlace) {
            $query = "CREATE (".str_replace(' ','_',strtolower($k)).":Place { name:'" . $k . "'";
                foreach($aPlace as $property => $value) {
                    $query.= ',' . strtolower($property) .":'" . $value ."'" ;
                }
            $query .= "}) ";
            $this->neo->sendCypherQuery($query);
        }

        // finish with rendering the query
        $this->view->set('query', $query);
        $this->app->render('neo4j/loadplaces.twig');


    }



    /**
     * List Places in the Graph
     * @param $placename
     */
    public function places($placename ='') {

        //find a list of places
        $query = "MATCH (p:Place ".( !empty($placename) ? "{name: '".$placename."'}" : '' ).") RETURN p LIMIT 100";

        $result = $this->neo->sendCypherQuery($query)->getRows();;

        $this->view->set('places', $result['p']);
        $this->app->render('neo4j/places.twig');

    }


    /**
     * @param int $nLimit
     */
    public function people( $nLimit = 200 ) {

        // Find people
        $query = "MATCH (p:Person) RETURN p LIMIT { limit } " ;
        // Send the query
        $result = $this->neo->sendCypherQuery($query,array('limit' => $nLimit));

        $res  = $result->getRows();


        $this->view->set('query',$query);
        $this->view->set('people', $res['p']);

        $this->app->render('neo4j/people.twig');
    }


    /**
     * create some random people
     * @param $num int
     */
    public function createpeople($num = 25) {

        $aPersonType = ['Hero','Villain','Civilian','Sidekick'];

        foreach (range(1, $num) as $idx) {
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


        //$this->view->set('query',$query);
        $this->people(200);

    }


    /**
     * add people to places with one of the following relationships
     * [BORN_IN, LIVED_IN, LIVES_IN, WORKS_IN, WORKED_IN, VISITED]
     */
    public function peopletoplaces() {

        $aRelationships = array('BORN_IN', 'LIVED_IN', 'LIVES_IN', 'WORKS_IN', 'WORKED_IN', 'VISITED');

        // Find some random people
        //$query = "MATCH (p:Person) RETURN p, rand() as r ORDER BY r LIMIT 25" ;

        $query = "MATCH (p:Person) WHERE NOT (p)-[]->(:Place) RETURN p, rand() as r ORDER BY r LIMIT 25" ;

        // Send the query
        $results = $this->neo->sendCypherQuery($query)->getResult();
        $nodes = $results->getNodes();

        foreach($nodes as $neoNode) {


            // GET 3 Random Places
            $queryplaces = "MATCH (place:Place) RETURN place, rand() as r ORDER BY r LIMIT 3";
            $placerows = $this->neo->sendCypherQuery($queryplaces)->getRows();
            $places = $placerows['place'];

            foreach($places as $place) {
                $sRelationship = $this->faker->randomElement($aRelationships);

                $setquery = "MATCH (p:Person {name:'" . $neoNode->getProperty('name') . "'}),(place:Place { name:'".$place['name']."' })
                             WHERE id(p)= " . $neoNode->getID() . "
                             CREATE (p)-[:" . $sRelationship . "]->(place)";
                //echo '<br />'.$setquery;
                $this->neo->sendCypherQuery($setquery);

            }


        }

        // List Status
        $this->index();




    }


    /**
     * relate people to people]
     */
    public function relatePeople() {


        // define people relationships
        $aRelationships = array(
            'IN_LOVE', 'FRIEND_OF', 'WORKS_WITH', 'SECRETLY_ADMIRES', 'HATES', 'DISLIKES', 'SERVES', 'RULES_OVER'
        );

        // get some random people with NO relationships yet
//        $query = "MATCH (p:Person) WHERE NOT (p)-[]-(:Person) RETURN p, rand() as r ORDER BY r";
        $query = "MATCH (p:Person) WHERE NOT (p)-[]-(:Person) RETURN p, rand() as r ORDER BY r LIMIT 10";
        $result = $this->neo->sendCypherQuery($query)->getResult();

        $nodes = $result->getNodes();
        
        foreach($nodes as $node) {
            $pquery = "MATCH (p:Person) WHERE NOT (p)-[]-(q:Person)  RETURN p, rand() as r ORDER BY r LIMIT 5";
            $result = $this->neo->sendCypherQuery($query)->getResult();
            $person = $result->getSingleNode();


            $sRelationship = $this->faker->randomElement($aRelationships);

            $setquery = "MATCH (p:Person {name:'" . $node->getProperty('name') . "'}),(q:Person { name:'".$person->getProperty('name')."' })
                             WHERE id(p)= " . $node->getID() . "
                             AND id(q)=" . $person->getID() . "
                             CREATE (p)-[:" . $sRelationship . "]->(q)";
            //echo '<br />'.$setquery;
            $this->neo->sendCypherQuery($setquery);
        }



    }


    /**
     * Find a person and list some information about them.
     * Who/What are they related to
     * @param $name name of person to find.
     */
    public function person($name) {



    }


    // SHortest Path between a person and a place
    public function shortestPath () {
        //get random person and place with NO relationship
        $query = "MATCH (a:Person), (b:Place) WHERE NOT (a)-[]-(b) RETURN a,b, rand() as r ORDER BY r LIMIT 1";

        $result = $this->neo->sendCypherQuery($query)->getResult();


        $person = $result->getNodes(array('label' => 'Person') );
        $place = $result->getNodes(array('label' => 'Place') );

        
        $person = $person['Person'][0];
        $place = $place['Place'][0];

        // The shortest path function
        $shortestquery = "MATCH (a:Person { name:'". $person->getProperty('name')."'}),
                                (b:Place { name:'".$place->getProperty('name')."' }),
                            p = shortestPath((a)-[*..15]-(b))
                          RETURN p";


        echo "Shortest route from ".  $person->getProperty('name') ." TO " . $place->getProperty('name') . " is via ";

        
        $r = $this->neo->sendCypherQuery($shortestquery);

	    $pathresult = $r->getResult();


	    $response = $this->neo->getResponse();
	    $body =  $response->getBody() ;
	    $aGraph = $body['results'][0]['data'][0]['graph'];
	    $aSteps = $body['results'][0]['data'][0]['rest'];


        $this->view->set('steps', $aSteps[0]);
	    $this->view->set('graph', $aGraph);



	    echo '<pre>';
	    print_r($aSteps);
	    echo '<hr style="width:20px"/>';

	    print_r($response);

	    $pathnodes = $pathresult->getNodes();
  	    $pathrelationships = $pathresult->getRelationships();


	    echo '</pre>';




    }





}