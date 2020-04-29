(function ($, Drupal) {
  Drupal.behaviors.articleSlider = {
    attach: function (context, settings) {
      if ($('#views-exposed-form-reports-block-slider', context).length > 0 && $(context).parents('.article-slider-wrapper').length > 0) {
        if ($(context).find('.article-slider').length == 0 && $('#layout-builder').length == 0) {
          $(context).closest('.layout').hide();
        }
        else {
          $(context).closest('.layout').show();
        }
      }
      $('.article-slider', context).once('article-slider').each(function () {
        var $articleSlider = $('.article-slider', context);
        var items = $articleSlider;


        var $container = $articleSlider.closest('.article-slide-container');
        var $sliderPrev = $container.find('.slide-arrow--prev');
        var $sliderNext = $container.find('.slide-arrow--next');
        if ($articleSlider.hasClass('slick-initialized')) {
          $articleSlider.slick('destroy');
        }

        var forceLazyLoad = function() {
          if (Drupal.forceLazyLoad) {
            Drupal.forceLazyLoad($('.article-slider')[0]);
          }
        }
        setTimeout(forceLazyLoad, 0);

        $articleSlider.slick({
          dots: false,
          arrows: false,
        });

        // Add trigger resize and scroll for correct display lazy load images.
        $articleSlider.on('afterChange', function(event, slick, currentSlide, nextSlide){
          $(window).trigger('resize scroll');
          forceLazyLoad();
        });

        $sliderPrev.on('click', function (e) {
          e.preventDefault();
          $articleSlider.slick('slickPrev');
          forceLazyLoad();
        });

        $sliderNext.on('click', function (e) {
          e.preventDefault();
          $articleSlider.slick('slickNext');
          forceLazyLoad();
        });
      });
    }
  };
})(jQuery, Drupal);