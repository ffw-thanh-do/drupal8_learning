(function ($, Drupal) {

  // Switch state for dropdown menu.
  Drupal.behaviors.header = {
    attach: function (context, settings) {
      // Max screen width for mobile devices.
      const mobileBreakpoint = 768;
      // Toggle class for show/hide mobile menu.
      const openClass = 'is-open';
      // Main site header.
      const $header = $('header.header', context);
      // Mobile menu button.
      const $mobileTrigger = $('.mobile-menu-trigger', $header);
      // Search trigger.
      const $searchTrigger = $('.search-trigger', $header);
      // Search form.
      const $searchForm = $('#block-search-form', $header);

      // Fire only for mobile devices.
      if (window.innerWidth <= mobileBreakpoint) {
        // Need to add additional touchdown event for iso devices.
        $mobileTrigger.on('click touchdown', function (e) {
          e.stopImmediatePropagation();
          e.preventDefault();
          // Show/hide mobile menu.
          $header.toggleClass(openClass);
          // Hide Search when dropdown menu open.
          $searchForm.removeClass(openClass);
        })
      }

      // Toggle Search.
      $searchTrigger.on('click touchdown', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $searchForm.toggleClass(openClass);
        $searchTrigger.toggleClass(openClass);
      })

    }
  };


  // Breadcrumbs.
  Drupal.behaviors.breadcrumbs = {
    attach: function (context, settings) {
      // Breadcrumb block.
      const $breadcrumbs = $('.breadcrumb', context);

      if (!$breadcrumbs.length) {
        return;
      }

      // Breadcrumb class for styling.
      const breadcrumbClass = 'breadcrumb';
      // Add class for parent layout block.
      $breadcrumbs.closest('.layout.layout-bsa-onecol').addClass(breadcrumbClass);
    }
  };

})(jQuery, Drupal);


