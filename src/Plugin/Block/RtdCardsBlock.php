<?php
namespace Drupal\rtd_cards\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**
 * Provides a 'RTD Cards' Block
 *
 * @Block(
 *   id = "rtd_cards_block",
 *   admin_label = @Translation("RTD Cards Block"),
 * )
 */
class RtdCardsBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        $build['#markup'] = $this->t('<div id="app">working</div>');

        return $build;
    }
}