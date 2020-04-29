(function ($, Drupal) {

  Drupal.behaviors.cleanEmptyViews = {
    attach: function (context, settings) {
      // Hide empty views.
      $('.layout', context).each(function () {
        if ($(this).find('.views-wrapper').length == 1 && $(this).find('.views-rows-wrapper').length == 0 && $(this).find('.block').length == 1) {
          if ($(this).find('.do-not-hide').length == 0) {
            $(this).hide();
            console.log('Layout hidden!');
          }
        }
      });

    }
  };

  Drupal.behaviors.videoBackground = {
    attach: function (context) {
      if ($('body').hasClass('internet-explorer') || $('body').hasClass('edge')) {
        $(context).find('video').once('video-autoplay').each(function(){
            var videoPlayer = $(this)[0];
            setTimeout(function(){
              if (videoPlayer.paused && $(videoPlayer).attr('autoplay') == 'autoplay') {
                videoPlayer.play();
              }
            }, 0);
        });
      }
    }
  }

  Drupal.behaviors.cookiepolicy = {
    attach: function (context) {
      $('a#cookie-close', context).once('cookiepolicy').on('click', function (event) {
        event.preventDefault();

        $.cookie('cookiepolicy', 1, {
          expires: 90,
          path: '/'
        });
        $('#block-cookiepolicy').remove();
      });

      if (!$.cookie('cookiepolicy')) {
        $('#block-cookiepolicy').show();
      }
    }
  };

  // Share block.
  Drupal.behaviors.shareBlock = {
    attach: function (context) {
      const $shareBlock = $('.share-block', context);

      if (!$shareBlock.length) {
        return false;
      }

      // Open class.
      const openClass = 'is-open';

      // Detect touchstart for mobile device.
      $shareBlock.on('click', function (e) {
        e.stopImmediatePropagation();
        $shareBlock.toggleClass(openClass);
      })

    }
  };

  // Collapse form on click filter link.
  Drupal.behaviors.collapseForm = {
    attach: function (context, settings) {
      const $formFilter = $('.collapse-expand-filter a', context);
      const openClass = 'is-open form-is-open';
      const mobileBreakpoint = 768;
      // Hide web form by default on mobile devices.
      if (window.innerWidth < mobileBreakpoint) {
        $formFilter.closest('.block').find('form.views-exposed-form').hide();
        $formFilter.addClass(openClass);
        if (Drupal.forceLazyLoad) {
          Drupal.forceLazyLoad($('.policy-items-container')[0]);
        }
      }

      $formFilter.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).toggleClass(openClass);
        $formExposed = $(this).closest('.block').find('form.views-exposed-form');
        $formExposed.slideToggle();
        if (Drupal.forceLazyLoad) {
          Drupal.forceLazyLoad($formExposed[0]);
        }
      });

      if ($formFilter.closest('.layout').hasClass('filter-default-closed')) {
        $formFilter.trigger('click');
      }

      var $formExposed = $('form.views-exposed-form', context);
      if ($formExposed.length > 0) {
        if (Drupal.forceLazyLoad) {
          Drupal.forceLazyLoad($formExposed[0]);
        }
      }
    }
  };

  Drupal.forceLazyLoad = function(element) {
    var settings = window.drupalSettings;
    var options = settings.lazy ? settings.lazy : {};
    options.loadInvisible = true;
    options.root = element;
    var lazyItem = new Blazy(options);
    lazyItem.revalidate();
    options.root = document;
    options.loadInvisible = false;
    new Blazy(options);

  }

  // Multiple select with checkboxes.
  Drupal.behaviors.multipleSelectClear = {
    attach: function (context, settings) {
      // Get multi selects.
      const $multiselect = $('select[multiple="multiple"]', context);
      $multiselect.each(function () {
        const $select = $(this);
        // Fire on select open dropdown.
        $select.on('chosen:showing_dropdown', function (evt, params) {
          // Find and detect click on selected item.
          $select.next().find('.result-selected').on('click', function () {
            // Get selected item index.
            let itemIndex = $(this).attr('data-option-array-index');
            // Find link with close button.
            let link = $select.next().find("a[data-option-array-index='" + itemIndex + "']");
            // Trigger click on delete item link.
            link.trigger('click');
          })
        });
        $select.attr('placeholder', $select.attr('data-placeholder'));
      });

    }
  };

  Drupal.behaviors.locationFilterOverride = {
    attach: function (context, settings) {
      $('.form-item-location select', context).on('chosen:ready', function (ev, args) {
        if (args.chosen.selected_option_count > 0) {
          var sender = args.chosen;
          sender.search_field.attr('placeholder', Drupal.t("Options selected"));
        }
      });
    }
  };

  // Header language switcher.
  $(document).ready(function () {
    var languageswitcher = $('#block-languageswitcher-2');
    languageswitcher.prepend('<div class="ls-display"></div>');
    var lsItems = $('ul.menu', languageswitcher);
    var lsItem = $('li', lsItems);
    var lsDisplay = $('.ls-display', languageswitcher);

    lsItem.each(function (key, value) {
      var self = $(value);
      if (self.hasClass('menu__item--active')) {
        var clonedItem = self.find('a').text();
        lsDisplay.html(clonedItem);
        self.remove();
      }
    });

    lsDisplay.click(function () {
      $(this).toggleClass('is-open');
      lsItems.slideToggle();
    });
  });

  // Header site switcher.
  $(document).ready(function () {
    var languageswitcher = $('#block-siteswitcher');
    var lsItems = $('ul.menu', languageswitcher);
    //var lsItem = $('li', lsItems);
    var lsDisplay = $('#block-siteswitcher-menu', languageswitcher);

    lsDisplay.click(function () {
      $(this).toggleClass('is-open');
      lsItems.slideToggle();
    });
  });

  // Additional class on layout wrapper for switch order for share block.
  Drupal.behaviors.shareBlockMove = {
    attach: function (context, settings) {
      // Get multi selects.
      const $shareBlock = $('.share-block-wrapper', context);

      if (!$shareBlock.length) {
        return;
      }

      $shareBlock.closest('.layout.layout-bsa-twocols').addClass('block-with-social-share');

    }
  };

  // Toggle policy item section. (mobile),
  Drupal.behaviors.policyIssueToggle = {
    attach: function (context, settings) {
      // Get Policy item block.
      const $policyBlock = $('.views-element-container .policy-item-section', context);

      if (!$policyBlock.length) {
        return;
      }
      // Toggle class.
      const openClass = 'is-open';
      // Add custom class for section.
      $policyBlock.closest('.layout.layout-bsa-onecol').addClass('toggled-policy-items-section');
      // Trigger link template.
      const $toggleLinkPattern = '<div class="toggle-section"><a href="#">' + Drupal.t('View Policy Issues') + '</a></div>';
      // Insert link before content.
      $policyBlock.before($toggleLinkPattern);
      // Get toggled link.
      const $toggleLink = $('.toggled-policy-items-section .toggle-section a');

      // Toggle class on section for show/hide policy items block.
      $toggleLink.on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('.toggled-policy-items-section .views-element-container').toggleClass(openClass);
      })

    }
  };

  // Additional class on layout wrapper for secondary navigation inside layout.
  Drupal.behaviors.secondaryContentMenu = {
    attach: function (context, settings) {
      // Get multi selects.
      const $secondaryNavigation = $('#block-bsa-content .layout ul.header-main-menu', context);

      if (!$secondaryNavigation.length) {
        return;
      }

      // Trigger link template.
      const $toggleLinkPattern = '<a href="#">' + $('.header .header-main-menu__link--active').text() + '</a>';
      // Insert link before content.
      $secondaryNavigation.before($toggleLinkPattern);
      $secondaryNavigation.closest('.layout.layout-bsa-onecol').addClass('secondary-content-menu');

      $('.secondary-content-menu nav > a').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.secondary-content-menu nav').toggleClass('is-open');
      })

    }
  };


  // Additional class for align two blocks with buttons.
  Drupal.behaviors.verticalAlignContactBSA = {
    attach: function (context, settings) {
      // Get multi selects.
      const $copyWithButton = $('.copy-with-button', context);

      if (!$copyWithButton.length) {
        return;
      }

      // Additional class for align items.
      $copyWithButton.each(function () {
        const $block = $(this);
        const $layout = $block.closest('.layout.layout-bsa-twocols')
        const copyWithLink = $layout.find('.copy-with-link');

        if (copyWithLink.length) {
          $layout.addClass('aligned-vertically');
        }
      })

    }
  };

  // Show more item.
  Drupal.behaviors.showMoreBSA = {
    attach: function (context, settings) {
      var $moreLink = $('.js-show-more .more-link', context),
          lessFlag = "less",
        showHiddenItemFunc = function (e) {
          e.preventDefault();

          if( $(this).hasClass('less') ){
            $(this).removeClass(lessFlag);
          } else {
            $(this).addClass(lessFlag);
          }
          $(this).siblings('div.toggleable').slideToggle();
        };

      $moreLink.on('click', showHiddenItemFunc);
    }
  };


})(jQuery, window.Drupal);
