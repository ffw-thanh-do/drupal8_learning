(function ($, Drupal) {

  // View more logic.
  Drupal.behaviors.policyViewMore = {
    attach: function (context, settings) {

      // Policy block.
      const $policyBlock = $('.policy-view-more-block', context);
      // Toggle link.
      const $link = $('.view-more-policy', $policyBlock);

      // Skip logic if more link not exist.
      if (!$link.length) {
        return;
      }

      // Block with main copy.
      const $targetSection = $('.dynamic-copy', $policyBlock);
      // Additional classes for show/hide content.
      const openClass = 'is-open';
      const rotateClass = 'is-rotated';

      // Click on View More link.
      $link.on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // Toggle classes for correct displaying.
        $link.toggleClass(rotateClass);
        $targetSection.toggleClass(openClass);

        // Switch copy in target link.
        $link.find('span.js-view-more').toggleClass('hidden');
        $link.find('span.js-view-less').toggleClass('hidden');
      })

    }
  };


})(jQuery, Drupal);
