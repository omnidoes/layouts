<?php

namespace Drupal\omni_layouts\Plugin\Layout;

/**
 * Configurable two column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class OneColumnLayout extends OmniLayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '100' => '100%',
    ];
  }

}
