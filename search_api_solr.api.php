<?php

/**
 * @file
 * Hooks provided by the Search API Solr search module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Lets modules alter a Solr search request before sending it.
 *
 * Apache_Solr_Service::search() is called afterwards with these parameters.
 * Please see this method for details on what should be altered where and what
 * is set afterwards.
 *
 * @param array $call_args
 *   An associative array containing all three arguments to the
 *   SearchApiSolrConnectionInterface::search() call ("query", "params" and
 *   "method") as references.
 * @param SearchApiQueryInterface $query
 *   The SearchApiQueryInterface object representing the executed search query.
 */
function hook_search_api_solr_query_alter(array &$call_args, SearchApiQueryInterface $query) {
  if ($query->getOption('foobar')) {
    $call_args['params']['foo'] = 'bar';
  }
}

/**
 * Change the way the index's field names are mapped to Solr field names.
 *
 * @param \Drupal\search_api\Index\IndexInterface $index
 *   The index whose field mappings are altered.
 * @param array $fields
 *   An associative array containing the index field names mapped to their Solr
 *   counterparts. The special fields 'search_api_id' and 'search_api_relevance'
 *   are also included.
 */
function hook_search_api_solr_field_mapping_alter(\Drupal\search_api\Index\IndexInterface $index, array &$fields) {
  if ($index->entity_type == 'node' && isset($fields['body:value'])) {
    $fields['body:value'] = 'text';
  }
}

/**
 * Change the way the index's field names are mapped to Solr field names that
 * store only the first value of the field.
 *
 * @param \Drupal\search_api\Index\IndexInterface $index
 *   The index whose field mappings are altered.
 * @param array $fields
 *   An associative array containing the index field names mapped to their Solr
 *   counterparts. The special fields 'search_api_id' and 'search_api_relevance'
 *   are also included.
 */
function hook_search_api_solr_single_value_field_mapping_alter(\Drupal\search_api\Index\IndexInterface $index, array &$fields) {
  if ($index->entity_type == 'node' && isset($fields['body:value'])) {
    $fields['body:value'] = 'text';
  }
}

/**
 * Alter Solr documents before they are sent to Solr for indexing.
 *
 * @param \Solarium\QueryType\Update\Query\Document\Document[] $documents
 *   An array of \Solarium\QueryType\Update\Query\Document\Document objects
 *   ready to be indexed, generated from $items array.
 * @param \Drupal\search_api\Index\IndexInterface $index
 *   The search index for which items are being indexed.
 * @param array $items
 *   An array of items being indexed.
 */
function hook_search_api_solr_documents_alter(array &$documents, \Drupal\search_api\Index\IndexInterface $index, array $items) {
  // Adds a "foo" field with value "bar" to all documents.
  foreach ($documents as $document) {
    $document->setField('foo', 'bar');
  }
}

/**
 * Lets modules alter the search results returned from a Solr search.
 *
 * @param \Drupal\search_api\Query\ResultSetInterface $results
 *   The results array that will be returned for the search.
 * @param \Drupal\search_api\Query\QueryInterface $query
 *   The SearchApiQueryInterface object representing the executed search query.
 * @param object $response
 *   The Solr response object.
 */
function hook_search_api_solr_search_results_alter(\Drupal\search_api\Query\ResultSetInterface $results, \Drupal\search_api\Query\QueryInterface $query, $response) {
  if (isset($response->facet_counts->facet_fields->custom_field)) {
    // Do something with $results.
  }
}

/**
 * Lets modules alter a Solr search request for a multi-index search.
 *
 * SearchApiSolrConnectionInterface::search() is called afterwards with these
 * parameters. Please see this method for details on what should be altered
 * where and what is set afterwards.
 *
 * @param array $call_args
 *   An associative array containing all three arguments to the
 *   SearchApiSolrConnectionInterface::search() call ("query", "params" and
 *   "method") as references.
 * @param SearchApiMultiQueryInterface $query
 *   The object representing the executed search query.
 */
function hook_search_api_solr_multi_query_alter(array &$call_args, SearchApiMultiQueryInterface $query) {
  if ($query->getOption('foobar')) {
    $call_args['params']['foo'] = 'bar';
  }
}

/**
 * Lets modules alter the search results returned from a multi-index search.
 *
 * @param array $results
 *   The results array that will be returned for the search.
 * @param SearchApiMultiQueryInterface $query
 *   The executed multi-index search query.
 * @param object $response
 *   The Solr response object.
 */
function hook_search_api_solr_multi_search_results_alter(array &$results, SearchApiMultiQueryInterface $query, $response) {
  if (isset($response->facet_counts->facet_fields->custom_field)) {
    // Do something with $results.
  }
}

/**
 * Provide Solr dynamic fields as Search API data types.
 *
 * This serves as a placeholder for documenting additional keys for
 * hook_search_api_data_type_info() which are recognized by this module to
 * automatically support dynamic field types from the schema.
 *
 * @return array
 *   In addition to the keys for the individual types that are defined by
 *   hook_search_api_data_type_info(), the following keys are regonized:
 *   - prefix: The Solr field name prefix to use for this type. Should match
 *     two existing dynamic fields definitions with names "{PREFIX}s_*" and
 *     "{PREFIX}m_*".
 *
 *@see hook_search_api_data_type_info()
 */
function search_api_solr_hook_search_api_data_type_info() {
  return array(
    // You can use any identifier you want here, but it makes sense to use the
    // field type name from schema.xml.
    'edge_n2_kw_text' => array(
      // Stock hook_search_api_data_type_info() info:
      'name' => t('Fulltext (w/ partial matching)'),
      'fallback' => 'text',
      // Dynamic field with name="te_*".
      'prefix' => 'te',
    ),
    'tlong' => array(
      // Stock hook_search_api_data_type_info() info:
      'name' => t('TrieLong'),
      'fallback' => 'integer',
      // Dynamic fields with name="its_*" and name="itm_*".
      'prefix' => 'it',
    ),
  );
}

/**
 * @} End of "addtogroup hooks".
 */
