<?php

namespace Drupal\rtd_cards\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class DefaultForm.
 */
class DefaultForm extends ConfigFormBase {

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
            '#description' => 'Which content type to use to display cards',
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
            // Custom validation handler for page 1.
            //'#validate' => ['::fapiExampleMultistepFormNextValidate'],
        ];

        return $form;

        //return parent::buildForm($form, $form_state);

    }

    public function rtdCardsFormNextSubmit(array &$form, FormStateInterface $form_state) {
        $form_state
            ->set('page_values', [
                // Keep only first step values to minimize stored data.
                'content_type' => $form_state->getValue('content_type')
            ])
            ->set('page_num', 2)
            // Since we have logic in our buildForm() method, we have to tell the form
            // builder to rebuild the form. Otherwise, even though we set 'page_num'
            // to 2, the AJAX-rendered form will still show page 1.
            ->setRebuild(TRUE);
    }

    public function rtdCardsPageTwo(array &$form, FormStateInterface $form_state) {

        //$page_values = $form_state->getValue('page_values');
        $content_type = $form_state->getValue('content_type');

        $fields = $this->getFields($content_type);

        $available_fields = $this->sortFields($fields);

        $t = 1;
        $form['content_type'] = [
            '#type' => 'hidden',
            '#default_value' => $content_type
        ];

        $form['text_field'] = [
            '#type' => 'radios',
            '#options' => $available_fields['text_fields'],
            '#title' => $this->t('Which text field do you want to use?'),
            //'#default_value' => $form_state->getValue('color', ''),
        ];

        $form['image_field'] = [
            '#type' => 'radios',
            '#options' => $available_fields['image_fields'],
            '#title' => $this->t('Which image field do you want to use?'),
            //'#default_value' => $form_state->getValue('color', ''),
        ];

        $form['taxonomy_field'] = [
            '#type' => 'radios',
            '#options' => $available_fields['taxonomy_fields'],
            '#title' => $this->t('Which taxonomy field do you want to use?'),
            '#required' => TRUE,
            //'#default_value' => $form_state->getValue('color', ''),
        ];

        $form['back'] = [
            '#type' => 'submit',
            '#value' => $this->t('Back'),
            // Custom submission handler for 'Back' button.
            '#submit' => ['::rtdCardsPageTwoBack'],
            // We won't bother validating the required 'color' field, since they
            // have to come back to this page to submit anyway.
            '#limit_validation_errors' => [],
        ];

        /*$form['submit'] = [
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => $this->t('Submit'),
        ];*/

        return parent::buildForm($form, $form_state);
    }

    public function rtdCardsPageTwoBack(array &$form, FormStateInterface $form_state) {
        $form_state
            // Restore values for the first step.
            ->setValues($form_state->get('page_values'))
            ->set('page_num', 1)
            // Since we have logic in our buildForm() method, we have to tell the form
            // builder to rebuild the form. Otherwise, even though we set 'page_num'
            // to 1, the AJAX-rendered form will still show page 2.
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

        $this->config('rtd_cards.settings')
            ->set('content_type', $form_state->getValue('content_type'))
            ->set('text_field', $form_state->getValue('text_field'))
            ->set('image_field', $form_state->getValue('image_field'))
            ->set('taxonomy_field', $form_state->getValue('taxonomy_field'))
            ->save();

        parent::submitForm($form, $form_state);
    }

    private function getContentTypes() {

        $node_types = NodeType::loadMultiple();
        $content_types = [];
        foreach ($node_types as $node_type) {
            $content_types[$node_type->get('type')] = $node_type->label();
        }
        return $content_types;

    }

    private function getFields($bundle) {

        $entityManager = \Drupal::service('entity_field.manager');

        $fields = $entityManager->getFieldDefinitions('node', $bundle);

        return $fields;

    }

    private function sortFields($fields) {

        $image_fields = [];

        $text_fields = [];

        $taxonomy_fields = [];

        $text_types = [
            'text',
            'text_long',
            'text_with_summary',
            'string',
            'string_long'
        ];

        foreach($fields as $field) {

            // check to see if field is not a base field
            if(!method_exists($field, 'get')) {

                continue;

            }

            $field_type = $field->getType();

            if($field_type == 'image') {

                $image_fields[$field->get('field_name')] = $field->label();

                continue;

            }

            if($field_type == 'entity_reference') {

                $tax_library = $field->get('settings')['handler_settings']['target_bundles']['tags'];

                $taxonomy_fields[$field->get('field_name')] = $field->label();

                continue;

            }

            if(in_array($field_type, $text_types)) {

                $text_fields[$field->get('field_name')] = $field->label();

            }

        }

        return [
            'image_fields' => $image_fields,
            'text_fields' => $text_fields,
            'taxonomy_fields' => $taxonomy_fields
        ];

    }

}