structWSF-Ontologies-Management-Tool
==================================

The Ontologies Management Tool (OMT) is a command line tool used to manage ontologies of a structWSF network instance. It can be used to list ontologies of a structWSF instance, to create/import new ones, to delete existing ones, to generate underlying ontological structures, etc.


Installing & Configuring the Ontologies Management Tool
-----------------------------------------------------

The Ontologies Management Tool can easily be installed on your server using the [OSF-Installer](https://github.com/structureddynamics/Open-Semantic-Framework-Installer):

```bash

  ./osf-installer --install-ontologies-management-tool -v
  
```

The OMT is using the [structWSF-PHP-API](https://github.com/structureddynamics/structWSF-PHP-API) library to communicate with any structWSF network instance. If the structWSF-PHP-API is not currently installed on your server, then follow these steps to download and install it on your server instance:

```bash

  ./osf-installer --install-structwsf-php-api -v 

```

Once both packages are installed, you will be ready to use the Ontologies Management Tool.

Usage Documentation
-------------------
```
Usage: omt [OPTIONS]


Usage examples:
    Load all ontologies: php sync.php --load-all --load-list="/data/ontologies/sync/ontologies.lst" --structwsf="http://localhost/ws/"
    Load one ontology: omt --load="http://purl.org/ontology/bibo/" --structwsf="http://localhost/ws/"
    List loaded ontologies: omt --list --structwsf=http://localhost/ws/"
    Deleting an ontology: omt --delete="http://purl.org/ontology/bibo/" --structwsf="http://localhost/ws/"
    Generate structures: omt --generate-structures="/data/ontologies/structure/" --structwsf="http://localhost/ws/"



Options:
-l, --load-all                          Load all the ontologies from a list of URLs

--load="[URL]"                          Load a single ontology

--list                                  List all loaded ontologies

--delete                                Show a list of loaded ontologies, select one for deletation

--delete="[URL]"                        Delete a specific ontology from the instance using its URI

--save                                  Show a list of loaded ontologies, select one for saving

--save="[URL]"                          Save a specific ontology from the instance using its URI

--generate-structures="[PATH]"          Generate all the derivate structures of the ontology.
                                        Specify where the structure files should be saved.

-h, --help                              Show this help section

General Options:
--structwsf="[URL]"                     (required) Target structWSF network endpoints URL.
                                                   Example: 'http://localhost/ws/'
--structwsf-query-extension="[CLASS]"   (optional) Query Extension Class (with its full namespace) to use for querying structwsf
                                                   Example: 'StructuredDynamics\structwsf
ramework\MYQuerierExtension'
Load Options:
--load-list="[FILE]"                    (required) File path where the list can be read.
                                                   The list is a series of space-seperated
                                                   URLs where ontologies files are accessible
--load-advanced-index="[BOOL]"          (optional) Default is false. If true, it means Advanced Indexation
                                                   is enabled. This means that the ontology's description
                                                   (so all the classes, properties and named individuals)
                                                   will be indexed in the other data management system in
                                                   structWSF. This means that all the information in these
                                                   ontologies will be accessible via the other endpoints
                                                   such as the Search and the SPARQL web service endpoints.
                                                   Enabling this option may render the creation process
                                                   slower depending on the size of the created ontology.
--load-force-reload="[BOOL]"            (optional) Default is false. If true, it means all the ontologies
                                                   will be deleted and reloaded/re-indexed in structWSF
```