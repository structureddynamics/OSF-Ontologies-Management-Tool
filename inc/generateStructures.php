<?php

  use \StructuredDynamics\structwsf\php\api\ws\ontology\read\OntologyReadQuery;

  function generateStructures($folder, $structwsf, $queryExtension = NULL)
  {
    include_once('getLoadedOntologies.php');

    cecho("Generating derivate ontological structures...\n", 'CYAN');
    
    $ontologiesClustered = getLoadedOntologies($structwsf);
    
    $ontologies = array();
    
    $ontologies = array_merge($ontologies, $ontologiesClustered['local'], $ontologiesClustered['reference'], $ontologiesClustered['admin']);
    
    // Generate the ironXML schemas
    foreach($ontologies as $ontology)
    {
      cecho("Generating ironXML schema of the ".$ontology['label']." ontology...\n", 'CYAN');
      
      $ontologyRead = new OntologyReadQuery($structwsf);
      
      $ontologyRead->ontology($ontology['uri'])
                   ->getIronXMLSchema()
                   ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                   
      if($ontologyRead->isSuccessful())
      {  
        $resultset = $ontologyRead->getResultset()->getResultset();    
        
        $ironXML = $resultset['unspecified'][$ontology['uri']]['http://purl.org/ontology/wsf#serializedIronXMLSchema']['0']['value'];
        
        cecho("Generated...\n", 'CYAN');
        
        $filename = substr($ontology['uri'], strripos($ontology['uri'], "/") + 1);
        $filename = substr($filename, 0, strripos($filename, "."));        
        
        file_put_contents(rtrim($folder, '/').'/'.$filename.'.xml', $ironXML);
        
        cecho("Saved to: ".rtrim($folder, '/').'/'.$filename.'.xml'."\n", 'CYAN');
      }
      else
      {
        $debugFile = md5(microtime()).'.error';
        file_put_contents('/tmp/'.$debugFile, var_export($ontologyRead, TRUE));
       
        @cecho('Can\'t get the ironXML schema structure for this ontology: '.$ontology.'. '. $ontologyRead->getStatusMessage() . 
               $ontologyRead->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
               
        continue;
      }        
    }
    
    // Generate the ironJSON schemas
    foreach($ontologies as $ontology)
    {
      cecho("Generating ironJSON schema of the ".$ontology['label']." ontology...\n", 'CYAN');
      
      $ontologyRead = new OntologyReadQuery($structwsf);
      
      $ontologyRead->ontology($ontology['uri'])
                   ->getIronJsonSchema()
                   ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                   
      if($ontologyRead->isSuccessful())
      {  
        $resultset = $ontologyRead->getResultset()->getResultset();    
        
        $ironJSON = $resultset['unspecified'][$ontology['uri']]['http://purl.org/ontology/wsf#serializedIronJSONSchema']['0']['value'];
        
        cecho("Generated...\n", 'CYAN');
        
        $filename = substr($ontology['uri'], strripos($ontology['uri'], "/") + 1);
        $filename = substr($filename, 0, strripos($filename, "."));        
        
        file_put_contents(rtrim($folder, '/').'/'.$filename.'.json', $ironJSON);
        
        cecho("Saved to: ".rtrim($folder, '/').'/'.$filename.'.json'."\n", 'CYAN');
        
        // Create the pre-existing JS script to load that schema in a Schema object.
        $ironJSONSchema = 'var ' . $filename . '_schema_srz = ' . $ironJSON . ";";
        $ironJSONSchema .= 'var ' . $filename . '_schema = new Schema(' . $filename . '_schema_srz);';

        // Special handling: make sure to convert all "%5C" characters into "\". This has to be done because
        // of the way the ProcessorXML.php (so, the DOMDocument API) works, and how the xml-encoding currently
        // works. Enventually, we should use the simplexml API in the processorXML script to properly
        // manage the encoding, and decoding of the XML data, *AND* of the HTML entities (it is the HTML
        // entities that are strangely manipulated in the DOMDocument API).
        $ironJSONSchema = str_replace("%5C", '\\', $ironJSONSchema);

        file_put_contents(rtrim($folder, "/") . "/" . $filename . ".js", $ironJSONSchema);        
        cecho("Saved to: ".rtrim($folder, '/').'/'.$filename.'.js'."\n", 'CYAN');        
      }
      else
      {
        $debugFile = md5(microtime()).'.error';
        file_put_contents('/tmp/'.$debugFile, var_export($ontologyRead, TRUE));
       
        @cecho('Can\'t get the ironJSON schema structure for this ontology: '.$ontology.'. '. $ontologyRead->getStatusMessage() . 
               $ontologyRead->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
               
        continue;
      }        
    }
    
    // Create the JS schema file.
    $schemaJS = ' /* List of attribute URIs that are generally used for labeling entities in different ontologies */
                var prefLabelAttributes = [
                  "http://www.w3.org/2004/02/skos/core#prefLabel",
                  "http://purl.org/ontology/iron#prefLabel",
                  "http://umbel.org/umbel#prefLabel",
                  "http://purl.org/dc/terms/title",
                  "http://purl.org/dc/elements/1.1/title",
                  "http://xmlns.com/foaf/0.1/name",
                  "http://xmlns.com/foaf/0.1/givenName",
                  "http://xmlns.com/foaf/0.1/family_name",
                  "http://www.geonames.org/ontology#name",
                  "http://www.w3.org/2000/01/rdf-schema#label"
                ];

                var altLabelAttributes = [
                  "http://www.w3.org/2004/02/skos/core#altLabel",
                  "http://purl.org/ontology/iron#altLabel",
                  "http://umbel.org/umbel#altLabel",
                ];

                var descriptionAttributes = [
                  "http://purl.org/ontology/iron#description",
                  "http://www.w3.org/2000/01/rdf-schema#comment",
                  "http://purl.org/dc/terms/description",
                  "http://purl.org/dc/elements/1.1/description",
                  "http://www.w3.org/2004/02/skos/core#definition"
                ];


                function Schema(sjson)
                {
                  // Define all prefixes of this resultset
                  this.prefixes = sjson.schema.prefixList;

                  // Unprefixize all URIs of this Schema
                  var resultsetJsonText = JSON.stringify(sjson);

                  for(var prefix in this.prefixes)
                  {
                    if(this.prefixes.hasOwnProperty(prefix))
                    {
                      var pattern = new RegExp(prefix+"_", "igm");
                      resultsetJsonText = resultsetJsonText.replace(pattern, this.prefixes[prefix]);
                    }
                  }

                  sjson = JSON.parse(resultsetJsonText);

                  this.attributes = sjson.schema.attributeList;

                  this.types = sjson.schema.typeList;

                  // Extend all attributes of this schema with additional functions
                  for(var i = 0; i < this.attributes.length; i++)
                  {
                    this.attributes[i].prefixes = this.prefixes;
                  }

                  // Extend all types of this schema with additional functions
                  for(var i = 0; i < this.types.length; i++)
                  {
                    this.types[i].prefixes = this.prefixes;
                  }
                }';

    file_put_contents(rtrim($folder, "/") . "/schema.js", $schemaJS);    

    // Generate PHP serialized classes hierarchy
    cecho("Generating PHP serialized classes hierarchy structure file...\n", 'CYAN');
    
    $ontologyRead = new OntologyReadQuery($structwsf);
    
    $ontologyRead->getSerializedClassHierarchy()
                 ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                 
    if($ontologyRead->isSuccessful())
    {  
      $resultset = $ontologyRead->getResultset()->getResultset();    
      
      $ironXML = $resultset['unspecified'][""]['http://purl.org/ontology/wsf#serializedClassHierarchy']['0']['value'];
      
      cecho("Generated...\n", 'CYAN');
      
      file_put_contents(rtrim($folder, '/').'/classHierarchySerialized.srz', $ironXML);
      
      cecho("Saved to: ".rtrim($folder, '/').'/classHierarchySerialized.srz'."\n", 'CYAN');
    }
    else
    {
      $debugFile = md5(microtime()).'.error';
      file_put_contents('/tmp/'.$debugFile, var_export($ontologyRead, TRUE));
     
      @cecho('Can\'t get the PHP serialized classes hierarchy structure file'. $ontologyRead->getStatusMessage() . 
             $ontologyRead->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
    }        
             
    // Generate PHP serialized properties hierarchy
    cecho("Generating PHP serialized properties hierarchy structure file...\n", 'CYAN');
    
    $ontologyRead = new OntologyReadQuery($structwsf);
    
    $ontologyRead->getSerializedPropertyHierarchy()
                 ->send(($queryExtension !== NULL ? $queryExtension : NULL));
                 
    if($ontologyRead->isSuccessful())
    {  
      $resultset = $ontologyRead->getResultset()->getResultset();    
      
      $ironXML = $resultset['unspecified'][""]['http://purl.org/ontology/wsf#serializedPropertyHierarchy']['0']['value'];
      
      cecho("Generated...\n", 'CYAN');
      
      file_put_contents(rtrim($folder, '/').'/propertyHierarchySerialized.srz', $ironXML);
      
      cecho("Saved to: ".rtrim($folder, '/').'/propertyHierarchySerialized.srz'."\n", 'CYAN');
    }
    else
    {
      $debugFile = md5(microtime()).'.error';
      file_put_contents('/tmp/'.$debugFile, var_export($ontologyRead, TRUE));
     
      @cecho('Can\'t get the PHP serialized properties hierarchy structure file'. $ontologyRead->getStatusMessage() . 
             $ontologyRead->getStatusMessageDescription()."\nDebug file: /tmp/$debugFile\n", 'RED');
    }  
    
    cecho("All structures generates!\n", 'CYAN');    
  }
  
?>
