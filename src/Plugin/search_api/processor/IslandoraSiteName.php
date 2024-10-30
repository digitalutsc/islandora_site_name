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
 *   id = "islandora_site_name",
 *   label = @Translation("Islandora Site Name"),
 *   description = @Translation("Add index for title of an Islandora site"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   hidden = false,
 * )
 */
class IslandoraSiteName extends ProcessorPluginBase
{
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
    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id,
        $plugin_definition
    ) {
        /** @var static $processor */
        $processor = parent::create(
            $container,
            $configuration,
            $plugin_id,
            $plugin_definition
        );
        $processor->httpClient = $container->get("http_client");
        $processor->configFactory = $container->get("config.factory");
        return $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyDefinitions(
        DatasourceInterface $datasource = null
    ) {
        $properties = [];

        if (!$datasource) {
            $definition = [
                "label" => $this->t("Islandora Site Name"),
                "description" => $this->t(
                    "Name of Islandora Site to be indexed to Solr"
                ),
                "type" => "string",
                "processor_id" => $this->getPluginId(),
            ];
            $properties[
                "search_api_islandora_site_name"
            ] = new ProcessorProperty($definition);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldValues(ItemInterface $item)
    {
        $datasourceId = $item->getDatasourceId();
        if (in_array($datasourceId,["entity:node", "entity:taxonomy_term"] )) {
            $siteTitle = $this->configFactory->get("system.site")->get("name");
            $fields = $this->getFieldsHelper()->filterForPropertyPath(
                $item->getFields(),
                null,
                "search_api_islandora_site_name"
            );
            foreach ($fields as $field) {
                if ($siteTitle) {
                    $field->addValue($siteTitle);
                }
            }
        }
    }
}
