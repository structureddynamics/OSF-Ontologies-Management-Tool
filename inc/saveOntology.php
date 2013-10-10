<?php

  use \StructuredDynamics\osf\php\api\ws\ontology\update\OntologyUpdateQuery;

  /**
  * Save an ontology from a OSF Web Services instance
  * 
  * @param mixed $uri URI of the ontology to save
  * @param mixed $osfWebServices URL of the OSF Web Services network
  * 
  * @return Return FALSE if the ontology couldn't be saved. Return TRUE otherwise.
  */  
  function saveOntology($uri, $osfWebServices, $queryExtension = NULL)
  {
    $ontologyUpdate = new OntologyUpdateQuery($osfWebServices);
    
    $ontologyUpdate->ontology($uri)
                   ->saveOntology()
                   ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                   
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
