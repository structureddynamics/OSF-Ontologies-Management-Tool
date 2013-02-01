<?php

  use \StructuredDynamics\structwsf\php\api\ws\ontology\update\OntologyUpdateQuery;

  /**
  * Save an ontology from a structWSF instance
  * 
  * @param mixed $uri URI of the ontology to save
  * @param mixed $structwsf URL of the structWSF network
  * 
  * @return Return FALSE if the ontology couldn't be saved. Return TRUE otherwise.
  */  
  function saveOntology($uri, $structwsf)
  {
    $ontologyUpdate = new OntologyUpdateQuery($structwsf);
    
    $ontologyUpdate->ontology($uri)
                   ->saveOntology()
                   ->send();
                   
    if($ontologyUpdate->isSuccessful())
    {
      cecho("Ontology successfully saved: $uri\n", 'CYAN');
    }
    else
    {
      $debugFile = md5(microtime()).'.error';
      file_put_contents('/tmp/'.$debugFile, var_export($ontologyUpdate, TRUE));
           
      @cecho('Can\'t save ontology '.$uri.'. '. $ontologyUpdate->getStatusMessage() . 
           $ontologyUpdate->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
           
      return(FALSE);
    }
    
    return(TRUE);        
  }
  
?>
