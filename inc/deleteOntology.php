<?php

  use \StructuredDynamics\osf\php\api\ws\ontology\delete\OntologyDeleteQuery;

  /**
  * Delete an ontology from a OSF Web Services instance
  * 
  * @param mixed $uri URI of the ontology to delete
  * @param mixed $credentials['osf-web-services'] URL of the OSF Web Services network
  * 
  * @return Return FALSE if the ontology couldn't be delete. Return TRUE otherwise.
  */
  function deleteOntology($uri, $credentials, $queryExtension = NULL)
  {
    $ontologyDelete = new OntologyDeleteQuery($credentials['osf-web-services'], $credentials['application-id'], $credentials['api-key'], $credentials['user']);
    
    $ontologyDelete->ontology($uri)
                   ->deleteOntology()
                   ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                   
    if($ontologyDelete->isSuccessful())
    {
      cecho("Ontology successfully deleted: $uri\n", 'CYAN');
    }
    else
    {
      if(strpos($ontologyDelete->getStatusMessageDescription(), 'WS-ONTOLOGY-DELETE-300') !== FALSE)
      {        
        cecho("$uri not currently loaded; skip deletation\n", 'BLUE');        
      }          
      else
      {
        $debugFile = md5(microtime()).'.error';
        file_put_contents('/tmp/'.$debugFile, var_export($ontologyDelete, TRUE));
             
        @cecho('Can\'t delete ontology '.$uri.'. '. $ontologyDelete->getStatusMessage() . 
             $ontologyDelete->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
             
        return(FALSE);
      }
    }
    
    return(TRUE);    
  }
?>
