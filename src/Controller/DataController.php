<?php

namespace Drupal\rtd_cards\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Queries for data routes.
 *
 * @package Drupal\rtd_cards\Controller
 */
class DataController extends ControllerBase {

  /**
   * Entity Type Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Protected configFactory variable.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Config Factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Autocomplete suggestions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns json response.
   */
  public function cards(Request $request) {

    $matches = [];

    $settings = $this->configFactory->get('rtd_cards.settings');

    $type = $settings->get('content_type');

    $taxonomy_field = $settings->get('taxonomy_field');

    $image_field = $settings->get('image_field');

    $text_field = $settings->get('text_field');

    $tid = $request->query->get('q');

    $storage = $this->entityTypeManager->getStorage('node');

    $query = $storage->getQuery();
    $query->condition('status', 1);
    $query->condition('type', $type);
    $query->range(0, 5);

    if ($tid) {
      $query->condition($taxonomy_field . '.target_id', $tid, 'IN');
    }

    $nids = $query->execute();

    $nodes = $storage->loadMultiple($nids);

    foreach ($nodes as $node) {

      $imageUri = NULL;

      $tids = [];

      $terms = [];

      // Image field is not required
      // if an image field is selected from admin page
      // and the content has the image field populated
      // populate path to image.
      if ($image_field && $node->$image_field->entity) {

        $imageUri = file_create_url($node->$image_field->entity->getFileUri());

      }

      if ($taxonomy_field) {

        $tags = $node->$taxonomy_field;

        foreach ($tags->referencedEntities() as $tag) {

          $tids[] = $tag->id();

          $terms[] = [
            'tid' => $tag->id(),
            'name' => $tag->getName(),
          ];

        }

      }

      $matches[] = [
        "id" => $node->id(),
        "name" => Xss::filter($node->getTitle()),
        "body" => $node->$text_field->value,
        "image" => [
          "path" => $imageUri ? $imageUri : "",
        ],
        "tids" => $taxonomy_field ? $tids : "",
        "terms" => $taxonomy_field ? $terms : "",
      ];

    }

    return new JsonResponse($matches);

  }

  /**
   * Term suggestions.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns json response.
   */
  public function terms() {

    $settings = $this->configFactory->get('rtd_cards.settings');

    $vocab = $settings->get('vocab');

    $matches = [];

    $storage = $this->entityTypeManager()->getStorage("taxonomy_term");

    $query = $storage->getQuery();

    $query->condition('vid', $vocab);

    $tids = $query->execute();

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($tids);

    foreach ($terms as $term) {

      $matches[] = [
        'value' => $term->id(),
        'label' => $term->getName(),
      ];

    }

    return new JsonResponse($matches);

  }

}
