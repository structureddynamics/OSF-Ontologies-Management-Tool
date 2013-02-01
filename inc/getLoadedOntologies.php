<?php

  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\OntologyReadQuery;
  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\GetLoadedOntologiesFunction;

  function getLoadedOntologies($structwsf)
  {
    $ontologyRead = new OntologyReadQuery($structwsf);
    
    $getLoadedOntologiesFunction = new GetLoadedOntologiesFunction();
    $getLoadedOntologiesFunction->modeDescriptions();
    
    $ontologyRead->getLoadedOntologies($getLoadedOntologiesFunction)
                 ->send();
        
    if($ontologyRead->isSuccessful())
    {
      $resultset = $ontologyRead->getResultset()->getResultset();
      
      $ontologies = array(
        'local' => array(),
        'reference' => array(),
        'admin' => array()
      );

      foreach($resultset['unspecified'] as $uri => $ontology)
      {
        $onto = array(
          'uri' => '',
          'label' => '',
          'modified' => false
        );        

        $ontologyType = 'local';
        
        if(isset($ontology['http://purl.org/ontology/sco#ontologyType']))
        {
          switch($ontology['http://purl.org/ontology/sco#ontologyType'][0]['uri'])
          {
            case "http://purl.org/ontology/sco#referenceOntology":
              $ontologyType = 'reference';
            break;
            case "http://purl.org/ontology/sco#administrativeOntology":
              $ontologyType = 'admin';
            break;
            case "http://purl.org/ontology/sco#localOntology":
              $ontologyType = 'local';
            break;
          }
        }
        
        $onto['uri'] = $uri;
        $onto['label'] = $ontology['prefLabel'];
        
        if(isset($ontology['http://purl.org/ontology/wsf#ontologyModified']))
        {
          $onto['modified'] = TRUE;
        }

        array_push($ontologies[$ontologyType], $onto);        
      }
      
      return($ontologies);
    }
    else
    {
      $debugFile = md5(microtime()).'.error';
      file_put_contents('/tmp/'.$debugFile, var_export($ontologyRead, TRUE));
     
      @cecho('Can\'t get loaded ontologies. '. $ontologyRead->getStatusMessage() . 
             $ontologyRead->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
             
      exit;
    }    
  }
  
  function showLoadedOntologies($ontologies)
  {
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
  
?>
