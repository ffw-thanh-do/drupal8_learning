(function ($, Drupal) {
  Drupal.behaviors.iconpicker = {
    attach: function (context, settings) {
      $('select[name*="field_icons"]', $(context)).fontIconPicker();
    }
  };
})(jQuery, Drupal);
