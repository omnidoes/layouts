<?php

/**
 * @file 
 * Contains Drupal\omni_layouts\Form\OmniLayoutsSettingsForm
 */
namespace Drupal\omni_layouts\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Module configuration form.
 */
class OmniLayoutsSettingsForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'omni_layouts.settings';


  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'omni_layouts_settings';
  }


  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }


  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // Gather the number of colors in the form already.
    $formstate_num_colors = $form_state->get('num_colors');
    $config_colors = $config->get('bgcolors');
      
    if($formstate_num_colors === NULL) {

      if($config_colors !== NULL) {
        $num_colors = count($config_colors);
        $bgcolors_field = $form_state->set('num_colors', $num_colors);
      } else {
        $bgcolors_field = $form_state->set('num_colors', 1);
        $num_colors = 1;
      }
    } else {
      $num_colors = $formstate_num_colors;
      $form_state->set('num_colors', $num_colors);
    }

    $bgcolors = ($config_colors !== NULL) && ($formstate_num_colors == NULL) ? 
      $config_colors : 
      $form_state->getValue('bgcolors');
    // Rekey array 
    $bgcolors = array_values($bgcolors);

    $form['#tree'] = TRUE;


    // Table

    $group_class = 'color-order-weight';

    $form['bgcolors'] = [
      '#type' => 'table',
      '#caption' => $this->t('Background Colors'),
      '#header' => array(
        $this->t('Color Name'),
        $this->t('Machine Name'),
        $this->t('Color Code'),
        $this->t('Weight'),
      ),
      '#prefix' => '<div id="bgcolors-table-wrapper">',
      '#suffix' => '</div>',
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class
        ]
      ]
    ];

      for ($i = 0; $i < $num_colors; $i++) {

        if (!isset($bgcolors[$i]['weight'])) {
          $bgcolors[$i]['weight'] = 0;
        }    

        $form['bgcolors'][$i]['#attributes']['class'][] = 'draggable';
        $form['bgcolors'][$i]['#weight'] = $bgcolors[$i]['weight'];

        $form['bgcolors'][$i]['name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Color Name'),
          '#title_display' => 'invisible',
          '#default_value' => $bgcolors[$i]['name'],
        ];

        $form['bgcolors'][$i]['machine_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Machine Name'),
          '#title_display' => 'invisible',
          '#disabled' => TRUE,
          '#value' => $bgcolors[$i]['machine_name']
        ];

        $form['bgcolors'][$i]['code'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Color Code'),
          '#title_display' => 'invisible',
          '#default_value' => $bgcolors[$i]['code'],
        ];

        $form['bgcolors'][$i]['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $bgcolors[$i]['name']]),
          '#title_display' => 'invisible',
          '#default_value' => $bgcolors[$i]['weight'],
          '#attributes' => ['class' => [$group_class]],
        ];

        // $form['bgcolors'][$i]['actions']['remove_row'] = [
        //   '#type' => 'submit',
        //   '#name' => "remove_row_$i",
        //   '#value' => $this->t('Remove'),
        //   '#submit' => ['::removeRow']
        // ];
      }


    // Actions

    $form['bgcolors_table']['actions'] = [
      '#type' => 'actions',
    ];
    $form['bgcolors_table']['actions']['add_color'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another color'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'bgcolors-table-wrapper',
      ],
    ];

    if ($num_colors > 1) {
      $form['bgcolors_table']['actions']['remove_color'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove last color'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'bgcolors-table-wrapper',
        ],
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    // $form['bgcolors'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Background Colors'),
    //   '#default_value' => $config->get('bgcolors')
    // ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['bgcolors'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $bgcolors_field = $form_state->get('num_colors');
    $add_button = $bgcolors_field + 1;
    $form_state->set('num_colors', $add_button);
    // Since our buildForm() method relies on the value of 'num_colors' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $bgcolors_field = $form_state->get('num_colors');
    if ($bgcolors_field > 1) {
      $remove_button = $bgcolors_field - 1;
      $form_state->set('num_colors', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'bgcolors' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove row" button.
   */
  public function removeRow(array &$form, FormStateInterface $form_state) {
    $rowToRemove = $form_state->getTriggeringElement()['#array_parents'][1];
    $form_state->unsetValue(['bgcolors', $rowToRemove]);

    $bgcolors_field = $form_state->get('num_colors');
    if ($bgcolors_field > 1) {
      $remove_button = $bgcolors_field - 1;
      $form_state->set('num_colors', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'bgcolors' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bgcolors = $form_state->getValue('bgcolors');
    
    // Create a machine_name value for each color record.
    foreach ($bgcolors as $key => $value) {
      if ($bgcolors[$key]['machine_name'] === NULL) {
        $bgcolors[$key]['machine_name'] = $this->transformToMachineName($value['name']);
      }
    }

    $this->config(static::SETTINGS)
      ->set('bgcolors', $bgcolors)
      ->save();

    parent::submitForm($form, $form_state);
  }


  private function transformToMachineName($value) {
    $new_value = strtolower($value);
    $new_value = preg_replace('/[^a-z0-9_]+/', '_', $new_value);
    return preg_replace('/_+/', '_', $new_value);
  }
}