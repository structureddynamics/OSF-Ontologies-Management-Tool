<?php

  use \StructuredDynamics\structwsf\php\api\ws\ontology\delete\OntologyDeleteQuery;

  /**
  * Delete an ontology from a structWSF instance
  * 
  * @param mixed $uri URI of the ontology to delete
  * @param mixed $structwsf URL of the structWSF network
  * 
  * @return Return FALSE if the ontology couldn't be delete. Return TRUE otherwise.
  */
  function deleteOntology($uri, $structwsf)
  {
    $ontologyDelete = new OntologyDeleteQuery($structwsf);
    
    $ontologyDelete->ontology($uri)
                   ->deleteOntology()
                   ->send();
                   
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
