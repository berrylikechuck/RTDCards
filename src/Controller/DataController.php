<?php
/**
@file
Contains \Drupal\rtd_cards\Controller\DataController.
 */

namespace Drupal\rtd_cards\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Xss;

class DataController extends ControllerBase {

    public function cards(Request $request) {

        $matches = [];

        $settings = \Drupal::config('rtd_cards.settings');

        $type = $settings->get('content_type');

        $taxonomy_field = $settings->get('taxonomy_field');

        $image_field = $settings->get('image_field');

        $text_field = $settings->get('text_field');

        $tid = $request->query->get('q');

        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1);
        $query->condition('type', $type);

        if($tid){
            $query->condition($taxonomy_field . '.target_id', $tid, 'IN');
        }

        $nids = $query->execute();

        foreach($nids as $nid){

            $node = Node::load($nid);

            $tids = [];

            $terms = [];

            if($image_field) {

                $imageUri = file_create_url($node->$image_field->entity->getFileUri());

            }

            if($taxonomy_field){

                foreach($node->$taxonomy_field->getValue() as $tid) {

                    $term = Term::load($tid['target_id']);

                    $tids[] = $tid['target_id'];

                    $terms[] = [
                        'tid' => $tid['target_id'],
                        'name' => $term->name->value
                    ];

                }

            }

            $matches[] = [
                "id" => $nid,
                "name" => Xss::filter($node->getTitle()),
                "body" => $node->$text_field->value,
                "image" => [
                    "path" => $image_field ? $imageUri : ""
                ],
                "tids" => $taxonomy_field ? $tids : "",
                "terms" => $taxonomy_field ? $terms : ""
            ];

        }

        return new JsonResponse($matches);

    }

    public function terms(Request $request) {

        $settings = \Drupal::config('rtd_cards.settings');

        $vocab = $settings->get('vocab');

        $matches = [];

        $query = \Drupal::entityQuery('taxonomy_term');

        $query->condition('vid', $vocab);

        $tids = $query->execute();

        foreach($tids as $tid){

            $term = Term::load($tid);

            $matches[] = [
                'value' => $tid,
                'label' => $term->getName()
            ];

        }

        return new JsonResponse($matches);

    }

}