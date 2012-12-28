<?php

  use \StructuredDynamics\structwsf\framework\Namespaces;
  use \StructuredDynamics\structwsf\framework\Resultset;
  use \StructuredDynamics\structwsf\framework\Subject;
  
  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\OntologyReadQuery;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\GetLoadedOntologiesFunction;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\create\OntologyCreateQuery;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\delete\OntologyDeleteQuery;
  
  /*
    
    The sync.php script does manage the management of ontologies in a structWSF instance.  
	
  */

  if(PHP_SAPI != 'cli')
  {
    die('This is a shell application, so make sure to run this application in your terminal.');
  }  
  
  // Get commandline options
  $arguments = getopt('h::l:', array('help::',
                                      'load-all::',
                                      'load-list::',
                                      'load-advanced-index::',
                                      'load-structwsf::',
                                      'load-force-reload::'));  
  
  // Displaying DSF's help screen if required
  if(isset($arguments['h']) || isset($arguments['help']))
  {
    cecho("Usage: php sync.php [OPTIONS]\n\n\n", 'WHITE');
    cecho("Usage examples: \n", 'WHITE');
    cecho("    Load all ontologies: php sync.php --load-all --load-list=\"/data/ontologies/sync/ontologies.lst --load-structwsf=http://localhost/ws/\"\n", 'WHITE');
    cecho("\n\n\nOptions:\n", 'WHITE');
    cecho("-l, --load-all                          Load all the ontologies from a list of URLs\n\n", 'WHITE');
    cecho("-h, --help                              Show this help section\n\n", 'WHITE');
    cecho("Load All Options:\n", 'WHITE');
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
    cecho("--load-structwsf=\"[URL]\"                (required) Target structWSF network endpoints URL.\n", 'WHITE');
    cecho("                                                   Example: 'http://localhost/ws/'\n", 'WHITE');
    cecho("--load-force-reload=\"[BOOL]\"            (optional) Default is false. If true, it means all the ontologies\n", 'WHITE');
    cecho("                                                   will be deleted and reloaded/re-indexed in structWSF\n", 'WHITE');
    
    exit;
  }
  
  // Load settings file
  $setup = parse_ini_file(getcwd()."/sync.ini", TRUE); 
  
  // Load the structWSF-PHP-API
  $structwsfFolder = rtrim($setup["config"]["structwsfFolder"], "/");
  
  include_once($structwsfFolder."/StructuredDynamics/SplClassLoader.php");   

  // Reload all ontologies
  if(isset($arguments['l']) || isset($arguments['load-all']))
  {
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['load-list']))
    {
      cecho("Missing the --load-list parameter for loading all the ontologies.\n", 'RED');  
      
      exit;
    }
    
    // Make sure the required arguments are defined in the arguments
    if(empty($arguments['load-structwsf']))
    {
      cecho("Missing the --load-structwsf parameter for loading all the ontologies.\n", 'RED');  
      
      exit;
    }
    
    // Load all the ontologies from the input list
    if(!file_exists($arguments['load-list']))
    {
      cecho("Input file of --load-list is not exising on this system.\n", 'RED');  
      
      exit;
    }
    
    $ontologiesUrls = explode(' ', file_get_contents($arguments['load-list']));
    
    foreach($ontologiesUrls as $url)
    {
      $url = str_replace(array("\r", "\n"), '', $url);
      
      cecho("Loading: $url\n", 'CYAN');
      
      if(isset($arguments['load-force-reload']) && filter_var($arguments['load-force-reload'], FILTER_VALIDATE_BOOLEAN))
      {
        cecho("Deleting ontology (reload foced): $url\n", 'CYAN');
        
        $ontologyDelete = new OntologyDeleteQuery($arguments['load-structwsf']);
        
        $ontologyDelete->ontology($url)
                       ->deleteOntology()
                       ->send();
                       
        if($ontologyDelete->isSuccessful())
        {
          cecho("Ontology successfully deleted: $url\n", 'CYAN');
        }
        else
        {
          if(strpos($ontologyCreate->getStatusMessageDescription(), 'WS-ONTOLOGY-DELETE-300') !== FALSE)
          {        
            cecho("$url not currently loaded; skip deletation\n", 'BLUE');        
          }          
          else
          {
            $debugFile = md5(microtime()).'.error';
            file_put_contents('/tmp/'.$debugFile, var_export($ontologyDelete, TRUE));
                 
            @cecho('Can\'t delete ontology '.$url.'. '. $ontologyDelete->getStatusMessage() . 
                 $ontologyDelete->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
                 
            continue;
          }
        }
      }
      
      $ontologyCreate = new OntologyCreateQuery($arguments['load-structwsf']);
      
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
  
     
  function cecho($text, $color="NORMAL", $return = FALSE)
  {
    $_colors = array(
      'LIGHT_RED'    => "[1;31m",
      'LIGHT_GREEN'  => "[1;32m",
      'YELLOW'       => "[1;33m",
      'LIGHT_BLUE'   => "[1;34m",
      'MAGENTA'      => "[1;35m",
      'LIGHT_CYAN'   => "[1;36m",
      'WHITE'        => "[1;37m",
      'NORMAL'       => "[0m",
      'BLACK'        => "[0;30m",
      'RED'          => "[0;31m",
      'GREEN'        => "[0;32m",
      'BROWN'        => "[0;33m",
      'BLUE'         => "[0;34m",
      'CYAN'         => "[0;36m",
      'BOLD'         => "[1m",
      'UNDERSCORE'   => "[4m",
      'REVERSE'      => "[7m",
    );    
    
    $out = $_colors["$color"];
    
    if($out == "")
    { 
      $out = "[0m"; 
    }
    
    if($return)
    {
      return(chr(27)."$out$text".chr(27)."[0m");
    }
    else
    {
      echo chr(27)."$out$text".chr(27).chr(27)."[0m";
    }
  }


?>