<?php

namespace Drupal\rtd_cards\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Admin form for RTD Cards.
 */
class DefaultForm extends ConfigFormBase {

  /**
   * Entity Type Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity Field Manager.
   *
   * @var Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Class constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param Drupal\Core\Entity\EntityFieldManager $entityFieldManager
   *   Entity Field Manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManager $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {

    return [
      'rtd_cards.settings',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'rtd_cards_default_form';

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {

      return self::rtdCardsPageTwo($form, $form_state);

    }

    $form_state->set('page_num', 1);

    $config = $this->config('rtd_cards.settings');

    $content_types = $this->getContentTypes();

    $content_type = $config->get('content_type');

    $form['content_type'] = [
      '#type' => 'radios',
      '#options' => $content_types,
      '#title' => $this->t('What content type to use?'),
      '#description' => $this->t('Which content type to use to display cards?'),
      '#default_value' => isset($content_type) ? $content_type : $form_state->getValue('content_type'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => ['::rtdCardsFormNextSubmit'],
    ];

    return $form;

  }

  /**
   * Next submit.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state.
   */
  public function rtdCardsFormNextSubmit(array &$form, FormStateInterface $form_state) {

    $form_state
      ->set('page_num', 2)
      // Rebuild form so AJAX-rendered form will show correct page.
      ->setRebuild(TRUE);

  }

  /**
   * Page Two Form Build.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state.
   *
   * @return array
   *   The form array.
   */
  public function rtdCardsPageTwo(array &$form, FormStateInterface $form_state) {

    $content_type = $form_state->getValue('content_type');

    $config = $this->config('rtd_cards.settings');

    $text_field = $config->get('text_field');

    $image_field = $config->get('image_field');

    $taxonomy_field = $config->get('taxonomy_field');

    $fields = $this->entityFieldManager
      ->getFieldDefinitions('node', $content_type);

    $available_fields = $this->sortFields($fields);

    $form['content_type'] = [
      '#type' => 'hidden',
      '#default_value' => $content_type,
    ];

    $form['text_field'] = [
      '#type' => 'radios',
      '#options' => $available_fields['text_fields'],
      '#title' => $this->t('Which text field do you want to use?'),
      '#default_value' => isset($text_field) ? $text_field : $form_state->getValue('text_field'),
      '#required' => TRUE,
    ];

    $form['image_field'] = [
      '#type' => 'radios',
      '#options' => $available_fields['image_fields'],
      '#title' => $this->t('Which image field do you want to use?'),
      '#default_value' => isset($image_field) ? $image_field : $form_state->getValue('image_field'),
    ];

    $form['taxonomy_field'] = [
      '#type' => 'radios',
      '#options' => $available_fields['taxonomy_fields'],
      '#title' => $this->t('Which taxonomy field do you want to use?'),
      '#default_value' => isset($taxonomy_field) ? $taxonomy_field : $form_state->getValue('taxonomy_field'),
      '#required' => TRUE,
    ];

    // Instead of having to call entity field manager again on submit
    // storing taxonomy field and associated vocabulary in json encoded array.
    $form['vocabs'] = [
      '#type' => 'hidden',
      '#default_value' => json_encode($available_fields['taxonomy_vocabs']),
    ];

    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => ['::rtdCardsPageTwoBack'],
      '#limit_validation_errors' => [],
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * The Page Two Back rebuild.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state.
   */
  public function rtdCardsPageTwoBack(array &$form, FormStateInterface $form_state) {

    $form_state
      ->set('page_num', 1)
      // Rebuild form so AJAX-rendered form will show correct page.
      ->setRebuild(TRUE);

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Decoding taxonomy field and associated vocab
    // to store vocab in a variable for use in json feed.
    $vocabs = json_decode($form_state->getValue('vocabs'), TRUE);

    $taxonomy_field = $form_state->getValue('taxonomy_field');

    // Getting vocab to store.
    $vocab = $vocabs[$taxonomy_field];

    $this->config('rtd_cards.settings')
      ->set('content_type', $form_state->getValue('content_type'))
      ->set('text_field', $form_state->getValue('text_field'))
      ->set('image_field', $form_state->getValue('image_field'))
      ->set('taxonomy_field', $taxonomy_field)
      ->set('vocab', $vocab)
      ->save();

    parent::submitForm($form, $form_state);

  }

  /**
   * Get available content types.
   *
   * @return array
   *   Returns array of content types.
   */
  private function getContentTypes() {

    $node_types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    $content_types = [];

    foreach ($node_types as $node_type) {

      $content_types[$node_type->get('type')] = $node_type->label();

    }

    return $content_types;

  }

  /**
   * Returns associative array of field values.
   *
   * @param array $fields
   *   The fields.
   *
   * @return array
   *   The array of values.
   */
  private function sortFields(array $fields) {

    $image_fields = [];

    $text_fields = [];

    $taxonomy_fields = [];

    $taxonomy_vocabs = [];

    $text_types = [
      'text',
      'text_long',
      'text_with_summary',
      'string',
      'string_long',
    ];

    foreach ($fields as $field) {

      // Check to see if field is not a base field.
      if (!method_exists($field, 'get')) {

        continue;

      }

      $field_type = $field->getType();

      if ($field_type == 'image') {

        $image_fields[$field->get('field_name')] = $field->label();

        continue;

      }

      if ($field_type == 'entity_reference') {

        $tax_library = $field->get('settings')['handler_settings']['target_bundles'];

        $vocab = current($tax_library);

        $field_name = $field->get('field_name');

        $taxonomy_fields[$field_name] = $field->label();

        $taxonomy_vocabs[$field_name] = $vocab;

        continue;

      }

      if (in_array($field_type, $text_types)) {

        $text_fields[$field->get('field_name')] = $field->label();

      }

    }

    return [
      'image_fields' => $image_fields,
      'text_fields' => $text_fields,
      'taxonomy_fields' => $taxonomy_fields,
      'taxonomy_vocabs' => $taxonomy_vocabs,
    ];

  }

}
