<?php

  use \StructuredDynamics\structwsf\framework\Namespaces;
  use \StructuredDynamics\structwsf\framework\Resultset;
  use \StructuredDynamics\structwsf\framework\Subject;
  
  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\OntologyReadQuery;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\GetLoadedOntologiesFunction;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\create\OntologyCreateQuery;
  
  include_once('inc/cecho.php');
  
  /*
    
    The sync.php script does manage the management of ontologies in a structWSF instance.  
  
  */

  if(PHP_SAPI != 'cli')
  {
    die('This is a shell application, so make sure to run this application in your terminal.');
  }  
  
  // Get commandline options
  $arguments = getopt('h::l::', array('help::',
                                      'load::',
                                      'list::',
                                      'delete::',
                                      'structwsf::',
                                      'load-all::',
                                      'load-list::',
                                      'load-advanced-index::',
                                      'load-force-reload::'));  
  
  // Displaying DSF's help screen if required
  if(isset($arguments['h']) || isset($arguments['help']))
  {
    cecho("Usage: php sync.php [OPTIONS]\n\n\n", 'WHITE');
    cecho("Usage examples: \n", 'WHITE');
    cecho("    Load all ontologies: php sync.php --load-all --load-list=\"/data/ontologies/sync/ontologies.lst\" --structwsf=\"http://localhost/ws/\"\n", 'WHITE');
    cecho("    Load one ontology: php sync.php --load=\"http://purl.org/ontology/bibo/\" --structwsf=\"http://localhost/ws/\"\n", 'WHITE');
    cecho("    List loaded ontologies: php sync.php --list --structwsf=http://localhost/ws/\"\n", 'WHITE');
    cecho("    Deleting an ontology: php sync.php --delete=\"http://purl.org/ontology/bibo/\" --structwsf=\"http://localhost/ws/\"\n", 'WHITE');
    cecho("\n\n\nOptions:\n", 'WHITE');
    cecho("-l, --load-all                          Load all the ontologies from a list of URLs\n\n", 'WHITE');
    cecho("--load=\"[URL]\"                          Load a single ontology\n\n", 'WHITE');
    cecho("--list                                  List all loaded ontologies\n\n", 'WHITE');
    cecho("--delete=\"[URL]\"                        Delete an ontology from the instance\n\n", 'WHITE');
    cecho("-h, --help                              Show this help section\n\n", 'WHITE');
    cecho("General Options:\n", 'WHITE');
    cecho("--structwsf=\"[URL]\"                     (required) Target structWSF network endpoints URL.\n", 'WHITE');
    cecho("                                                   Example: 'http://localhost/ws/'\n", 'WHITE');
    cecho("Load Options:\n", 'WHITE');
    cecho("--load-list=\"[FILE]\"                    (required) File path where the list can be read.\n", 'WHITE');
    cecho("                                                   The list is a series of space-seperated\n", 'WHITE');
    cecho("                                                   URLs where ontologies files are accessible\n", 'WHITE');
    cecho("--load-advanced-index=\"[BOOL]\"          (optional) Default is false. If true, it means Advanced Indexation\n", 'WHITE');
    cecho("                                                   is enabled. This means that the ontology's description \n", 'WHITE');
    cecho("                                                   (so all the classes, properties and named individuals) \n", 'WHITE');
    cecho("                                                   will be indexed in the other data management system in \n", 'WHITE');
    cecho("                                                   structWSF. This means that all the information in these \n", 'WHITE');
    cecho("                                                   ontologies will be accessible via the other endpoints \n", 'WHITE');
    cecho("                                                   such as the Search and the SPARQL web service endpoints. \n", 'WHITE');
    cecho("                                                   Enabling this option may render the creation process \n", 'WHITE');
    cecho("                                                   slower depending on the size of the created ontology.\n", 'WHITE');
    cecho("--load-force-reload=\"[BOOL]\"            (optional) Default is false. If true, it means all the ontologies\n", 'WHITE');
    cecho("                                                   will be deleted and reloaded/re-indexed in structWSF\n", 'WHITE');
    
    exit;
  }
  
  // Load settings file
  $setup = parse_ini_file(getcwd()."/sync.ini", TRUE); 
  
  // Load the structWSF-PHP-API
  $structwsfFolder = rtrim($setup["config"]["structwsfFolder"], "/");
  
  include_once($structwsfFolder."/StructuredDynamics/SplClassLoader.php");   
 
  // Delete loaded ontology
  if(isset($arguments['delete']))
  {
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['structwsf']))
    {
      cecho("Missing the --structwsf parameter for deleting the ontology.\n", 'RED');  
      
      exit;
    }    
    
    cecho("Deleting ontology: ".$arguments['delete']."\n", 'CYAN');
    
    include_once('inc/deleteOntology.php');
    
    $deleted = deleteOntology($arguments['delete'], $arguments['structwsf']);  
  }  
 
  // List loaded ontologies
  if(isset($arguments['list']))
  {
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['structwsf']))
    {
      cecho("Missing the --structwsf parameter for listing ontologies.\n", 'RED');  
      
      exit;
    }    
    
    include_once('inc/getLoadedOntologies.php');
    
    $ontologies = getLoadedOntologies($arguments['structwsf']);
    
    $nb = 0;
    
    cecho("Local Ontologies: \n", 'WHITE');
    
    foreach($ontologies['local'] as $ontology)
    {
      $nb++;
      
      cecho("  ($nb) ".$ontology['label'].'  '.cecho('('.$ontology['uri'].')', 'CYAN', TRUE).'  '.($ontology['modified'] ? '  '.cecho('[modified; not saved]', 'YELLOW', TRUE) : '')."\n", 'WHITE');
    }
    
    cecho("\nReference Ontologies: \n", 'WHITE');
    
    foreach($ontologies['reference'] as $ontology)
    {
      $nb++;
      
      cecho("  ($nb) ".$ontology['label'].'  '.cecho('('.$ontology['uri'].')', 'CYAN', TRUE).'  '.($ontology['modified'] ? '  '.cecho('[modified; not saved]', 'YELLOW', TRUE) : '')."\n", 'WHITE');
    }
    
    cecho("\nAdministrative Ontologies: \n", 'WHITE');
    
    foreach($ontologies['admin'] as $ontology)
    {
      $nb++;
      
      cecho("  ($nb) ".$ontology['label'].'  '.cecho('('.$ontology['uri'].')', 'CYAN', TRUE).'  '.($ontology['modified'] ? '  '.cecho('[modified; not saved]', 'YELLOW', TRUE) : '')."\n", 'WHITE');
    }
  }
  
  // Reload all ontologies
  if(isset($arguments['l']) || isset($arguments['load-all']) || isset($arguments['load']))
  {
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['load-list']) && !isset($arguments['load']))
    {
      cecho("Missing the --load-list parameter for loading all the ontologies.\n", 'RED');  
      
      exit;
    }
    
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['structwsf']))
    {
      cecho("Missing the --structwsf parameter for loading all the ontologies.\n", 'RED');  
      
      exit;
    }
    
    // Load all the ontologies from the input list
    if(!file_exists($arguments['load-list']) && !isset($arguments['load']))
    {
      cecho("Input file of --load-list is not exising on this system.\n", 'RED');  
      
      exit;
    }
    
    $ontologiesUrls = array();
    
    if(!isset($arguments['load']))
    {
      $ontologiesUrls = explode(' ', file_get_contents($arguments['load-list']));
    }
    else
    {
      // There is only a single ontology URL to load for this command
      array_push($ontologiesUrls, $arguments['load'])  ;
    }
    
    foreach($ontologiesUrls as $url)
    {
      $url = str_replace(array("\r", "\n"), '', $url);
      
      cecho("Loading: $url\n", 'CYAN');
      
      if(isset($arguments['load-force-reload']) && filter_var($arguments['load-force-reload'], FILTER_VALIDATE_BOOLEAN))
      {
        cecho("Deleting ontology (reload forced): $url\n", 'CYAN');
        
        include_once('inc/deleteOntology.php');
        
        $deleted = deleteOntology($url, $arguments['structwsf']);
        
        if(!$deleted)
        {
          continue;
        }
      }
      
      $ontologyCreate = new OntologyCreateQuery($arguments['structwsf']);
      
      $ontologyCreate->uri($url)
                     ->enableReasoner();
                     
      if(isset($arguments['load-advanced-index']) && filter_var($arguments['load-advanced-index'], FILTER_VALIDATE_BOOLEAN))
      {
        $ontologyCreate->enableAdvancedIndexation();
      }
      else
      {
        $ontologyCreate->disableAdvancedIndexation();
      }
      
      $ontologyCreate->send();
      
      if($ontologyCreate->isSuccessful())      
      {
        cecho("$url loaded!\n", 'BLUE');        
      }
      else
      {
        if(strpos($ontologyCreate->getStatusMessageDescription(), 'WS-ONTOLOGY-CREATE-302') !== FALSE)
        {        
          cecho("$url already loaded!\n", 'BLUE');        
        }
        else
        {
          $debugFile = md5(microtime()).'.error';
          file_put_contents('/tmp/'.$debugFile, var_export($ontologyCreate, TRUE));
               
          @cecho('Can\'t load ontology file '.$url.'. '. $ontologyCreate->getStatusMessage() . 
               $ontologyCreate->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
        }
      }
    }
  }
?>