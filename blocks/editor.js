/**
 * Advanced Testimonial — block editor script (no build step).
 *
 * Uses wp.element.createElement directly (no JSX) and ServerSideRender for a
 * live preview that matches the frontend output exactly.
 *
 * @package AdvancedTestimonial
 */
( function ( wp ) {
	'use strict';

	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps      = wp.blockEditor.useBlockProps;
	var PanelBody          = wp.components.PanelBody;
	var SelectControl      = wp.components.SelectControl;
	var RangeControl       = wp.components.RangeControl;
	var ToggleControl      = wp.components.ToggleControl;
	var ServerSideRender   = wp.serverSideRender;
	var __                 = wp.i18n.__;

	var data = window.advancedTestimonialBlock || { groups: [ { value: '', label: 'All groups' } ] };

	var layoutOptions = [
		{ label: __( 'Grid', 'advanced-testimonial' ), value: 'grid' },
		{ label: __( 'List', 'advanced-testimonial' ), value: 'list' },
		{ label: __( 'Card', 'advanced-testimonial' ), value: 'card' },
		{ label: __( 'Carousel', 'advanced-testimonial' ), value: 'carousel' },
		{ label: __( 'Masonry', 'advanced-testimonial' ), value: 'masonry' },
		{ label: __( 'Spotlight', 'advanced-testimonial' ), value: 'spotlight' }
	];

	var orderOptions = [
		{ label: __( 'Newest first', 'advanced-testimonial' ), value: 'desc' },
		{ label: __( 'Oldest first', 'advanced-testimonial' ), value: 'asc' },
		{ label: __( 'Random', 'advanced-testimonial' ), value: 'random' }
	];

	function toggle( props, attr, label ) {
		return el( ToggleControl, {
			label: label,
			checked: !! props.attributes[ attr ],
			onChange: function ( value ) {
				var update = {};
				update[ attr ] = value;
				props.setAttributes( update );
			}
		} );
	}

	registerBlockType( 'advanced-testimonial/testimonials', {
		edit: function ( props ) {
			var a          = props.attributes;
			var set        = props.setAttributes;
			var blockProps = useBlockProps();

			var inspector = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __( 'Layout', 'advanced-testimonial' ), initialOpen: true },
					el( SelectControl, {
						label: __( 'Layout', 'advanced-testimonial' ),
						value: a.layout,
						options: layoutOptions,
						onChange: function ( v ) { set( { layout: v } ); }
					} ),
					el( SelectControl, {
						label: __( 'Group', 'advanced-testimonial' ),
						value: a.group,
						options: data.groups,
						onChange: function ( v ) { set( { group: v } ); }
					} ),
					el( RangeControl, {
						label: __( 'Columns', 'advanced-testimonial' ),
						value: a.columns,
						min: 1,
						max: 6,
						onChange: function ( v ) { set( { columns: v } ); }
					} ),
					el( RangeControl, {
						label: __( 'Number to show', 'advanced-testimonial' ),
						value: a.limit,
						min: 1,
						max: 30,
						onChange: function ( v ) { set( { limit: v } ); }
					} ),
					el( SelectControl, {
						label: __( 'Order', 'advanced-testimonial' ),
						value: a.order,
						options: orderOptions,
						onChange: function ( v ) { set( { order: v } ); }
					} ),
					el( RangeControl, {
						label: __( 'Autoplay seconds (0 = off)', 'advanced-testimonial' ),
						help: __( 'Carousel and Spotlight only.', 'advanced-testimonial' ),
						value: a.autoplay,
						min: 0,
						max: 15,
						onChange: function ( v ) { set( { autoplay: v } ); }
					} )
				),
				el(
					PanelBody,
					{ title: __( 'Display', 'advanced-testimonial' ), initialOpen: false },
					toggle( props, 'showRating', __( 'Show rating', 'advanced-testimonial' ) ),
					toggle( props, 'showImage', __( 'Show client photo', 'advanced-testimonial' ) ),
					toggle( props, 'showCompany', __( 'Show company', 'advanced-testimonial' ) ),
					toggle( props, 'showDesignation', __( 'Show designation', 'advanced-testimonial' ) ),
					toggle( props, 'showLocation', __( 'Show location', 'advanced-testimonial' ) ),
					toggle( props, 'showDate', __( 'Show date', 'advanced-testimonial' ) ),
					toggle( props, 'showVerified', __( 'Show verified badge', 'advanced-testimonial' ) ),
					toggle( props, 'showWebsite', __( 'Show website button', 'advanced-testimonial' ) )
				)
			);

			var preview = el( ServerSideRender, {
				block: 'advanced-testimonial/testimonials',
				attributes: a
			} );

			return el( Fragment, {}, inspector, el( 'div', blockProps, preview ) );
		},

		save: function () {
			return null;
		}
	} );
}( window.wp ) );
