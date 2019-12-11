<?php

namespace Drupal\omni_layouts\Plugin\Layout;

/**
 * Configurable two column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ThreeColumnLayout extends OmniLayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '33-34-33' => '33%/34%/33%',
      '25-25-50' => '25%/25%/50%',
      '50-25-25' => '50%/25%/25%',
      '25-50-25' => '25%/50%/25%',
    ];
  }

}
