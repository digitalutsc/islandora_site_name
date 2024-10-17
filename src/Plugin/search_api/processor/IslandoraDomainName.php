<?php

namespace Drupal\islandora_site_name\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;

/**
 * Adds the item's view count to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "islandora_domain_name",
 *   label = @Translation("Islandora Domain Name"),
 *   description = @Translation("Add index for title of an Islandora Domain"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   hidden = false,
 * )
 */
class IslandoraDomainName extends ProcessorPluginBase {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Theme settings config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->httpClient = $container->get('http_client');
    $processor->configFactory = $container->get('config.factory');
    return $processor;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];
    if (!$datasource) {
      $definition = [
        'label' => $this->t('Islandora Domain Name'),
        'description' => $this->t('Name of Islandora Domain to be indexed to Solr'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
//         'processor_id' => 'joijoin3i_3djn',
      ];
      $properties['search_api_islandora_domain_name'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $datasourceId = $item->getDatasourceId();
    if ($datasourceId == 'entity:node') {
      $domain = \Drupal::request()->getSchemeAndHttpHost();
      $fields = $this->getFieldsHelper()->filterForPropertyPath($item->getFields(), NULL,
      'search_api_islandora_domain_name');
      foreach ($fields as $field) {
        if ($domain) {
          $field->addValue($domain);
        }
      }
    }
  }


}

