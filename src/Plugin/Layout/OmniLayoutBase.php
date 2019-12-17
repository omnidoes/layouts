<?php

namespace Drupal\omni_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Render\Markup;

/**
 * Base class of layouts with configurable widths.
 *
 * @internal
 *   Plugin classes are internal.
 */
abstract class OmniLayoutBase extends LayoutDefault implements PluginFormInterface {
  
  const SETTINGS = 'omni_layouts.settings';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $width_classes = array_keys($this->getWidthOptions());
    return [
      'alignment' => [
        'vertical' => null,
        'horizontal' => null
      ],
      'background' => [
        'color' => 'none'
      ],
      'layout' => [
        'column_widths' => array_shift($width_classes),
        'row_width' => '100',
        'vertical_padding_level' => 'regular'
      ],
      'extra' => [
        'css_class' => null
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $config = \Drupal::config(static::SETTINGS);

    // This method receives a sub form state instead of the full form state.
    // There is an ongoing discussion around this which could result in the
    // passed form state going back to a full form state. In order to prevent
    // future breakage because of a core update we'll just check which type of
    // FormStateInterface we've been passed and act accordingly.
    // @See https://www.drupal.org/node/2798261
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;

    /*
     * Alignment Section
     */
    $form['alignment'] = [
      '#type' => 'details',
      '#title' => $this->t('Alignment'),
    ];
    
      $form['alignment']['vertical'] = [
        '#type' => 'radios',
        '#tree' => TRUE,
        '#title' => $this->t('Vertical'),
        '#default_value' => $this->configuration['alignment']['vertical'],
        '#options' => [
          'top' => $this->t('Top'),
          'middle' => $this->t('Middle'),
          'bottom' => $this->t('Bottom'),
        ]
      ];

      $form['alignment']['horizontal'] = [
        '#type' => 'radios',
        '#tree' => TRUE,
        '#title' => $this->t('Horizontal'),
        '#default_value' => $this->configuration['alignment']['horizontal'],
        '#options' => [
          'left' => $this->t('Left'),
          'center' => $this->t('Center'),
          'right' => $this->t('Right'),
        ]
      ];
    
    /*
    * Background Section
    */
    $form['background'] = [
      '#type' => 'details',
      '#title' => $this->t('Background'),
      '#attached' => [
        'library' => [
          'omni_layouts/swatch'
        ],
      ]
    ];

      $bgcolors = $config->get('bgcolors');

      if (!empty($bgcolors)) {
        $css = $this->getBackgroundColorCss($bgcolors);

        $form['background']['styles'] = [
          '#type' => 'html_tag',
          '#tag' => 'style',
          '#attributes' => [
            'media' => $css['media'],
          ],
          '#value' => Markup::create($css['style']),
        ];
      }

      $backgroundColorOptions = []; 
      
      if (!empty($bgcolors)) {
        foreach($bgcolors as $key => $value) {
          $swatch = "<span class='swatch swatch--" . $value['machine_name'] . "'></span>";
          $backgroundColorOptions[$value['machine_name']] = $this->t($value['name']) . ' ' . $swatch;
        };
      }

      // Add the default option
      $backgroundColorOptions = array('none' => $this->t('None')) + $backgroundColorOptions;
    
      $form['background']['color'] = [
        '#type' => 'radios',
        '#tree' => TRUE,
        '#title' => $this->t('Background Color'),
        '#default_value' => $this->configuration['background']['color'],
        '#options' => $backgroundColorOptions,
      ];

    /*
    * Layout Section
    */
    $form['layout'] = [
      '#type' => 'details',
      '#title' => $this->t('Layout'),
    ];

      $form['layout']['vertical_padding_level'] = [
        '#type' => 'select',
        '#tree' => TRUE,
        '#title' => $this->t('Vertical Padding Level'),
        '#default_value' => $this->configuration['layout']['vertical_padding_level'],
        '#options' => [
          'none' => 'None',
          'small' => 'Small',
          'regular' => 'Regular',
          'large' => 'Large',
          'extralarge' => 'Extra Large',
        ]
      ];
    
      // Column widths Section
      $form['layout']['column_widths'] = [
        '#type' => 'select',
        '#tree' => TRUE,
        '#title' => $this->t('Column widths'),
        '#default_value' => $this->configuration['layout']['column_widths'],
        '#options' => $this->getWidthOptions(),
        '#description' => $this->t('Choose the column widths for this layout.'),
      ];
      
      $form['layout']['row_width'] = [
        '#type' => 'select',
        '#tree' => TRUE,
        '#title' => $this->t('Row width'),
        '#default_value' => $this->configuration['layout']['row_width'],
        '#description' => $this->t('Choose the width of the row\'s content.'),
        '#options' => [
          '100' => $this->t('100%'),
          '75' => $this->t('75%'),
          '67' => $this->t('67%'),
          '50' => $this->t('50%'),
          '33' => $this->t('33%'),
          '25' => $this->t('25%')
        ]
      ];
       
      $defaultRowConstrain = $complete_form_state->getValue(['layout', 'row_constrain'], $this->configuration['layout']['row_constrain']);
      if ($defaultRowConstrain === null) { 
        $defaultRowConstrain = TRUE;
      }

      $form['layout']['row_constrain'] = [
        '#type' => 'checkbox',
        '#tree' => TRUE,
        '#title' => $this->t('Constrain row max-width'),
        '#default_value' => $defaultRowConstrain,
        '#description' => $this->t('Enable to constrain the width of this row to the maximum layout width.'),
      ];
      

    /*
     * Extra Section
     */
    $form['extra'] = [
      '#type' => 'details',
      '#title' => $this->t('Extra'),
    ];

      $form['extra']['css_class'] = [
        '#type' => 'textfield',
        '#tree' => TRUE,
        '#title' => $this->t('Custom Class'),
        '#default_value' => $this->configuration['extra']['css_class'],
        '#description' => $this->t('Enter custom css classes for this row. Seperate multiple classes by a space and do not include a period.')
      ];
    
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['alignment']['vertical'] = $form_state->getValue(['alignment', 'vertical']);
    $this->configuration['alignment']['horizontal'] = $form_state->getValue(['alignment', 'horizontal']);
    $this->configuration['background']['color'] = $form_state->getValue(['background', 'color']);
    $this->configuration['layout']['vertical_padding_level'] = $form_state->getValue(['layout', 'vertical_padding_level']);
    $this->configuration['layout']['column_widths'] = $form_state->getValue(['layout', 'column_widths']);
    $this->configuration['layout']['row_width'] = $form_state->getValue(['layout', 'row_width']);
    $this->configuration['layout']['row_constrain'] = $form_state->getValue(['layout', 'row_constrain']);
    $this->configuration['extra']['css_class'] = $form_state->getValue(['extra', 'css_class']);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $config = \Drupal::config(static::SETTINGS);
    $build = parent::build($regions);

    // Adding classes
    $build['#attributes']['class'] = [
      'layout',
      $this->getPluginDefinition()->getTemplate(),
      $this->getPluginDefinition()->getTemplate() . '-' . $this->configuration['layout']['column_widths'],
      'layout--width-' . $this->configuration['layout']['row_width'],
    ];

    // Adding inline styles for background color
    if($this->configuration['background']['color'] !== "none") {
      $bgColorIndex = array_search(
        $this->configuration['background']['color'], 
        array_column($config->get('bgcolors'), 'machine_name') 
      );
      $build['section']['#attributes']['style'] = [
        'background-color: ' . $config->get('bgcolors')[$bgColorIndex]['code']
      ];
    }

    $build['section']['#attributes']['class'] = [
      'block',
      'block--layout',
      'layout--vpadding-' . $this->configuration['layout']['vertical_padding_level'],
      $this->configuration['extra']['css_class']
    ];

    $build['wrapper']['#attributes']['class'] = [
      'layout__container'
    ];

    if($this->configuration['layout']['row_constrain'] === 1) {
      $build['wrapper']['#attributes']['class'][] = 'layout__container--maxdesk';
    }

    if($this->configuration['alignment']['horizontal'] !== NULL) {
      $build['wrapper']['#attributes']['class'][] = 'layout--align';
      $build['wrapper']['#attributes']['class'][] = 'layout--align-h-' . $this->configuration['alignment']['horizontal'];
    }

    if($this->configuration['alignment']['vertical'] !== NULL) {
      $build['wrapper']['#attributes']['class'][] = 'layout--align';
      $build['wrapper']['#attributes']['class'][] = 'layout--align-v-' . $this->configuration['alignment']['vertical'];
    }

    return $build;
  }

  
  /**
   * Creates a string of CSS styles which style each background
   * color configured through the settings.
   *
   * @param array $colors
   *   The Colors configured through the form.
   * @return array
   *   An array containing renderable CSS
   */
  private function getBackgroundColorCss($colors) {

    $style = '';

    foreach ($colors as $key => $color) {
      $selector = ".swatch--" . $color['machine_name'];
      // $colorValue = $color['code'];
      $style .= sprintf(
        '%s { background-color: %s !important;}', 
        $selector,
        $color['code']
      );
    }

    return [
      'type' => 'inline',
      'style' => $style,
      'media' => 'screen',
      'group' => CSS_THEME,
    ];
  }


  /**
   * Gets the width options for the configuration form.
   *
   * The first option will be used as the default 'column_widths' configuration
   * value.
   *
   * @return string[]
   *   The width options array where the keys are strings that will be added to
   *   the CSS classes and the values are the human readable labels.
   */
  abstract protected function getWidthOptions();

}
