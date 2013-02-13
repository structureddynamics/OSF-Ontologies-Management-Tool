structWSF-Ontologies-Management-Tool
==============================================
```
Usage: php sync.php [OPTIONS]


Usage examples:
    Load all ontologies: ./omt --load-all --load-list="/data/ontologies/sync/ontologies.lst --load-structwsf=http://localhost/ws/"



Options:
-l, --load-all                          Load all the ontologies from a list of URLs

-h, --help                              Show this help section

Load All Options:
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
--load-structwsf="[URL]"                (required) Target structWSF network endpoints URL.
                                                   Example: 'http://localhost/ws/'
--load-force-reload="[BOOL]"            (optional) Default is false. If true, it means all the ontologies
                                                   will be deleted and reloaded/re-indexed in structWSF
```