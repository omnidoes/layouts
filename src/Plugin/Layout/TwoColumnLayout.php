<?php

namespace Drupal\omni_layouts\Plugin\Layout;

/**
 * Configurable two column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class TwoColumnLayout extends OmniLayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '50-50' => '50%/50%',
      '33-67' => '33%/67%',
      '42-58' => '42%/58%',
      '67-33' => '67%/33%',
      '25-75' => '25%/75%',
      '75-25' => '75%/25%',
    ];
  }

}
