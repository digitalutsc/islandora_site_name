# Islandora Site Name
Search API processor for indexing Islandora site names with Solr.

## Setup
* Navigate to *Manage processors for search index*
(Configuration > Search and Metadata > Search API > [YOUR INDEX] > Processors).
* Tick *Islandora Site Name* checkbox.
* Save configuration.
* Navigate to *Manage fields for search index*
(Configuration > Search and Metadata > Search API > [YOUR INDEX] > Fields).
* Add *Islandora Site Name* field (under `Add fields` button).
* Save configuration.
* Reindex items.
* Navigate to *Ajax Solr Search Config Form* (`admin/config/search/ajax_solr`).
* Add `islandora_site_name` to facets (machine name might be slightly different
for you) and set corresponding *Solr Field Label*.
* Save configuration.

## Notes
* Currently, this module only indexes site name from the HTML title value of
the nodes rather than the Solr site field. It does this by checking for the
value after `|` in the title. If the title is not in this form, the site name
will not be indexed.
