<?php

/**
 * @file
 * Contains \Drupal\search_api_solr\Tests\SearchApiSolrTest.
 */

namespace Drupal\search_api_solr\Tests;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\search_api\Query\ResultSetInterface;
use Drupal\search_api\Tests\ExampleContentTrait;
use Drupal\search_api_db\Tests\SearchApiDbTest;

/**
 * Tests index and search capabilities using the Solr search backend.
 */
class SearchApiSolrTest extends SearchApiDbTest {

  /**
   * A Search API server ID.
   *
   * @var string
   */
  protected $serverId = 'solr_search_server';

  /**
   * A Search API index ID.
   *
   * @var string
   */
  protected $indexId = 'solr_search_index';

  /**
   * Whether a Solr core is available for testing. Mostly needed because Drupal
   * testbots do not support this.
   *
   * @var bool
   */
  protected $solrAvailable = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('search_api_solr', 'search_api_test_solr');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Test "Solr search" module',
      'description' => 'Tests indexing and searching with the "Solr search" module.',
      'group' => 'Search API',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(array('search_api_test_solr'));

    try {
      $server = Server::load($this->serverId);
      if ($server->getBackend()->ping()) {
        $this->solrAvailable = TRUE;
      }
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Tests various indexing scenarios for the Solr search backend.
   */
  public function testFramework() {
    // Only run the tests if we have a Solr core available.
    if ($this->solrAvailable) {
      parent::testFramework();
    }
    else {
      $this->pass('Error: The Solr instance could not be found. Please enable a multi-core one on http://localhost:8983/solr/d8');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function indexItems($index_id) {
    /** @var \Drupal\search_api\Index\IndexInterface $index */
    $index = Index::load($index_id);
    $index->setOption('index_directly', TRUE);
    return $index->index();
  }

  /**
   * {@inheritdoc}
   */
  protected function clearIndex() {
    $server = Server::load($this->serverId);
    $index = Index::load($this->indexId);
    $index->clear();

    // Since \Drupal\search_api_solr\Plugin\SearchApi\Backend\SearchApiSolrBackend::deleteAllItems()
    // schedules a commit operation which will be executed at the end of the PHP
    // request, we need to send a commit message to Solr manually.
    $server->getBackend()->commit();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkServerTables() {
    // The Solr backend doesn't create any database tables.
  }

  protected function updateIndex() {
    // The parent assertions don't make sense for the Solr backend.
  }

  /**
   * {@inheritdoc}
   */
  protected function searchSuccess2() {
    // This method tests the 'min_chars' option of the Database backend, which
    // we don't have in Solr.
    // @todo Copy tests from the Apachesolr module which create Solr cores on
    // the fly with various schemas.
  }

  /**
   * {@inheritdoc}
   */
  protected function uninstallModule() {
    // See whether clearing the server works.
    // Regression test for #2156151.
    $server = Server::load($this->serverId);
    $index = Index::load($this->indexId);
    $server->deleteAllItems($index);

    // Since \Drupal\search_api_solr\Plugin\SearchApi\Backend\SearchApiSolrBackend::deleteAllItems()
    // schedules a commit operation which will be executed at the end of the PHP
    // request, we need to send a commit message to Solr manually.
    $server->getBackend()->commit();

    $query = $this->buildSearch();
    $results = $query->execute();
    $this->assertEqual($results->getResultCount(), 0, 'Clearing the server worked correctly.');
  }

  /**
   * {@inheritdoc}
   */
  protected function assertIgnored(ResultSetInterface $results, array $ignored = array(), $message = 'No keys were ignored.') {
    // Nothing to do here since the Solr backend doesn't keep a list of ignored
    // fields.
  }

}
