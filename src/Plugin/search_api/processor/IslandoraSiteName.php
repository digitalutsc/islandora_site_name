<?php

namespace Drupal\islandora_site_name\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\Html;

/**
 * Adds the item's view count to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "islandora_site_name",
 *   label = @Translation("Islandora Site Name"),
 *   description = @Translation("Add index for title of an Islandora site"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   hidden = false,
 * )
 */
class IslandoraSiteName extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $processor;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Islandora Site Name'),
        'description' => $this->t('Name of Islandora Site to be indexed to Solr'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_islandora_site_name'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * Gets the page title from a URL.
   *
   * @param string $url
   *  The URL to get the title from.
   *
   * @return string
   *  Page title.
   */
  protected function getPageTitle(String $url) {
    $request = \Drupal::httpClient()->get($url);
    $dom = Html::load($request->getBody());
    if ($dom) {
      $list = $dom->getElementsByTagName('title');
      if ($list->length > 0) {
        return $list->item(0)->textContent;
      }
    }
    return NULL;
  }

  /**
   * Gets the site title from a page title.
   *
   * @param string $title
   *  The page title to get the site title from.
   *
   * @return string
   *  Site title.
   */
  protected function extractSiteTitle(?String $title) {
    if (!is_null($title)) {
      $titleParts = explode('|', $title);
      if (count($titleParts) > 1) {
        return $titleParts[1];
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $datasourceId = $item->getDatasourceId();
    if ($datasourceId == 'entity:node') {
      $node = $item->getOriginalObject()->getValue();
      $url = $node->toUrl()->setAbsolute()->toString();

      $title = $this->getPageTitle($url);
      $siteTitle = $this->extractSiteTitle($title);

      $fields = $this->getFieldsHelper()->filterForPropertyPath($item->getFields(), NULL,
      'search_api_islandora_site_name');
      foreach ($fields as $field) {
        if ($siteTitle) {
          $field->addValue($siteTitle);
        }
      }
    }
  }

}
