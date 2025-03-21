<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;


class MenuOffscreen extends BlockBase {
	const BLOCK_NAME        = 'kubio/menu-offscreen';
	const CONTAINER         = 'container';
	const OFFSCREEN         = 'offscreen';
	const OFFSCREEN_OVERLAY = 'offscreenOverlay';
	const ICON_WRAPPER      = 'iconWrapper';
	const ICON              = 'icon';


	private $offscreen_id = null;

	public function __construct( $block, $autoload = true ) {
		parent::__construct( $block, $autoload );
		$this->offscreen_id = uniqid( 'kubio-offscreen-' );
	}

	public function mapPropsToElements() {
		$icon      = $this->getAttribute( 'icon', 'font-awesome/navicon' );
		$direction = $this->getAttribute( 'openSide', 'right' );
		$js_props  = Utils::useJSComponentProps( 'offcanvas' );

		$width = $this->getStyle(
			'width',
			250,
			array(
				'styledComponent' => self::OFFSCREEN,
			)
		);

		$width = array_merge(
			array(
				'value' => 300,
				'unit'  => 'px',
			),
			(array) $width
		);

		return array(
			self::ICON              => array( 'name' => $icon ),
			self::ICON_WRAPPER      => array_merge(
				array(
					'data-target'               => "#{$this->offscreen_id}",
					'data-target-id'            => "{$this->offscreen_id}",
					'data-direction'            => esc_attr( $direction ),
					'data-width'                => esc_attr( "{$width['value']}{$width['unit']}" ),
					'data-offcanvas-overlay-id' => "{$this->offscreen_id}-overlay",
					'aria-label'                => __( 'Mobile Menu', 'kubio' ),
				),
				$js_props
			),
			self::OFFSCREEN_OVERLAY => array(
				'id'        => "{$this->offscreen_id}-overlay",
				'className' => 'offscreen-overlay',
			),
			self::OFFSCREEN         => array(
				'id'        => $this->offscreen_id,
				'className' => 'hide',
			),
		);
	}

	public function canRender() {

		$menu_block = Registry::getInstance()->getLastBlockOfName( DropDownMenuBlock::BLOCK_NAME );
		if ( ! $menu_block ) {
			$menu_block = Registry::getInstance()->getLastBlockOfName( AccordionMenuBlock::BLOCK_NAME );
		}

		if ( $menu_block ) {
			$offscreen_style = $menu_block->getAttribute( 'showOffscreenMenuOn', false );

			if ( $offscreen_style === 'has-offcanvas-none' ) {
				return false;
			}
		}

		return parent::canRender();
	}

	public function render( $wp_block ) {

		return parent::render( $wp_block );
	}
}

Registry::registerBlock(
	__DIR__,
	MenuOffscreen::class
);
