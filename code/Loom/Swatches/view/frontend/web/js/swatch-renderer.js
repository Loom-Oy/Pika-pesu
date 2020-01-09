/*
 * @package    Loom_Swatches
 * @copyright  Loom Oy - 2020
 */

define([
    'jquery',
    'jquery/ui',
    'magento-swatch.renderer'
  ], function($){
  
    $.widget('loom.SwatchRenderer', $.mage.SwatchRenderer, { 
  
          /**
           * Event for swatch options
           *
           * @param {Object} $this
           * @param {Object} $widget
           * @private
           */
          _OnClick: function ($this, $widget) {
              var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                  $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                  $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                  attributeId = $parent.attr('attribute-id'),
                  $input = $parent.find('.' + $widget.options.classes.attributeInput);
  
              if ($widget.inProductList) {
                  $input = $widget.productForm.find(
                      '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                  );
              }
  
              if ($this.hasClass('disabled')) {
                  return;
              }
  
              if ($this.hasClass('selected')) {
                  $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                  $input.val('');
                  $label.text('');
                  $this.attr('aria-checked', false);
              } else {
                  $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                  $label.text($this.attr('option-label'));
                  $input.val($this.attr('option-id'));
                  $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                  $this.addClass('selected');
                  $widget._toggleCheckedAttributes($this, $wrapper);
              }
  
              $widget._Rebuild();
  
              // Get SKU from current product
              var sku = $widget.options.jsonConfig.sku[this.getProduct()];

              // Update sku if not empty
              if(sku != ''){
                  $('[itemprop="sku"]').html(sku);
              }
  
              if ($widget.element.parents($widget.options.selectorProduct)
                      .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
              ) {
                  $widget._UpdatePrice();
              }
  
              $widget._loadMedia();
              $input.trigger('change');
          }
  
      });
  
    return $.loom.SwatchRenderer;
  });