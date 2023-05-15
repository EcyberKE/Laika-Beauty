'use strict';

class Carousel {
  CarouselSlickQuickView() {
    jQuery('#yith-quick-view-content .woocommerce-product-gallery__wrapper, .yith-quick-view.yith-modal .woocommerce-product-gallery__wrapper').each(function () {
      let _this = jQuery(this);

      if (_this.children().length == 0 || _this.hasClass("slick-initialized")) {
        return;
      }

      var _config = {};
      _config.slidesToShow = 1;
      _config.infinite = false;
      _config.focusOnSelect = true;
      _config.dots = true;
      _config.arrows = true;
      _config.adaptiveHeight = true;
      _config.mobileFirst = true;
      _config.vertical = false;
      _config.cssEase = 'ease';
      _config.prevArrow = '<button type="button" class="slick-prev"><i class="' + greenmart_settings.quick_view.prev + '"></i></button>';
      _config.nextArrow = '<button type="button" class="slick-next"><i class="' + greenmart_settings.quick_view.next + '"></i></button>';
      _config.settings = "unslick";
      _config.rtl = _this.parent('.woocommerce-product-gallery-quick-view').data('rtl') === 'yes';
      $(".variations_form").on("woocommerce_variation_select_change", function () {
        _this.slick("slickGoTo", 0);
      });

      _this.slick(_config);
    });
  }

  CarouselSlick() {
    var _this = this;

    if (jQuery(".owl-carousel[data-carousel=owl]:visible").length === 0) return;
    jQuery('.owl-carousel[data-carousel=owl]:visible:not(.scroll-init)').each(function () {
      _this._initCarouselSlick(jQuery(this));
    });
    jQuery('.owl-carousel[data-carousel=owl]:visible.scroll-init').waypoint(function () {
      var $this = $($(this)[0].element);

      _this._initCarouselSlick($this);
    }, {
      offset: '100%'
    });
  }

  _initCarouselSlick(_this2) {
    var _this = this;

    if (_this2.hasClass("slick-initialized")) {
      return;
    }

    if (!jQuery.browser.mobile) {
      _this2.slick(_this._getSlickConfigOption(_this2));
    } else if (!_this2.data('unslick')) {
      _this2.slick(_this._getSlickConfigOption(_this2));
    }
  }

  _getSlickConfigOption(el) {
    var slidesToShow = jQuery(el).data('items'),
        rows = jQuery(el).data('rows') ? parseInt(jQuery(el).data('rows')) : 1,
        desktop = jQuery(el).data('desktopslick') ? jQuery(el).data('desktopslick') : slidesToShow,
        desktopsmall = jQuery(el).data('desktopsmallslick') ? jQuery(el).data('desktopsmallslick') : slidesToShow,
        tablet = jQuery(el).data('tabletslick') ? jQuery(el).data('tabletslick') : slidesToShow,
        landscape = jQuery(el).data('landscapeslick') ? jQuery(el).data('landscapeslick') : 2,
        mobile = jQuery(el).data('mobileslick') ? jQuery(el).data('mobileslick') : 2,
        navleft = '<button type="button" class="slick-prev slick-arrow"><i class="' + jQuery(el).data('navleft') + '"></i></button>',
        navright = '<button type="button" class="slick-next slick-arrow"><i class="' + jQuery(el).data('navright') + '"></i></button>',
        unslick = jQuery(el).data('unslick') && jQuery(window).width() < 768 ? jQuery(el).data('unslick') : false;
    let enonumber = slidesToShow < jQuery(el).children().length ? true : false,
        enonumber_mobile = 2 < jQuery(el).children().length ? true : false;
    let pagination = enonumber ? Boolean(jQuery(el).data('pagination')) : false,
        mobile_pagination = enonumber_mobile ? Boolean(jQuery(el).data('pagination')) : false,
        nav = enonumber ? Boolean(jQuery(el).data('nav')) : false,
        mobile_nav = enonumber_mobile ? Boolean(jQuery(el).data('nav')) : false,
        loop = enonumber ? Boolean(jQuery(el).data('loop')) : false,
        auto = enonumber ? Boolean(jQuery(el).data('auto')) : false;
    var _config = {};
    _config.dots = pagination;
    _config.arrows = nav;
    _config.infinite = loop;
    _config.speed = jQuery(el).data('speed') ? jQuery(el).data('speed') : 300;
    _config.autoplay = auto;
    _config.autoplaySpeed = jQuery(el).data('autospeed') ? jQuery(el).data('autospeed') : 2000;
    _config.cssEase = 'ease';
    _config.slidesToShow = slidesToShow;
    _config.slidesToScroll = slidesToShow;
    _config.mobileFirst = true;
    _config.vertical = false;
    _config.prevArrow = navleft;
    _config.nextArrow = navright;
    _config.rtl = jQuery('html').attr('dir') == 'rtl';

    if (rows > 1) {
      _config.slidesToShow = 1;
      _config.slidesToScroll = 1;
      _config.rows = rows;
      _config.slidesPerRow = slidesToShow;
      var settingsFull = {
        slidesPerRow: slidesToShow
      },
          settingsDesktop = {
        slidesPerRow: desktop
      },
          settingsDesktopsmall = {
        slidesPerRow: desktopsmall
      },
          settingsTablet = {
        slidesPerRow: tablet
      },
          settingsLandscape = unslick ? "unslick" : {
        slidesPerRow: landscape
      },
          settingsMobile = unslick ? "unslick" : {
        slidesPerRow: mobile
      };
    } else {
      var settingsFull = {
        slidesToShow: slidesToShow,
        slidesToScroll: slidesToShow
      },
          settingsDesktop = {
        slidesToShow: desktop,
        slidesToScroll: desktop
      },
          settingsDesktopsmall = {
        slidesToShow: desktopsmall,
        slidesToScroll: desktopsmall
      },
          settingsTablet = {
        slidesToShow: tablet,
        slidesToScroll: tablet
      },
          settingsLandscape = unslick ? "unslick" : {
        slidesToShow: landscape,
        slidesToScroll: landscape,
        dots: mobile_pagination,
        arrows: mobile_nav
      };
      settingsMobile = unslick ? "unslick" : {
        slidesToShow: mobile,
        slidesToScroll: mobile,
        dots: mobile_pagination,
        arrows: mobile_nav
      };
    }

    _config.responsive = [{
      breakpoint: 1600,
      settings: settingsFull
    }, {
      breakpoint: 1199,
      settings: settingsDesktop
    }, {
      breakpoint: 991,
      settings: settingsDesktopsmall
    }, {
      breakpoint: 767,
      settings: settingsTablet
    }, {
      breakpoint: 575,
      settings: settingsLandscape
    }, {
      breakpoint: 0,
      settings: settingsMobile
    }];
    return _config;
  }

  getSlickTabs() {
    $('ul.nav-tabs li a').on('shown.bs.tab', event => {
      let carouselItemTab = $($(event.target).attr("href")).find(".owl-carousel[data-carousel=owl]:visible");
      let carouselItemDestroy = $($(event.relatedTarget).attr("href")).find(".owl-carousel[data-carousel=owl]");

      if (!carouselItemTab.hasClass("slick-initialized")) {
        carouselItemTab.slick(this._getSlickConfigOption(carouselItemTab));
      }

      if (carouselItemDestroy.hasClass("slick-initialized")) {
        carouselItemDestroy.slick('unslick');
      }
    });
  }

}

class Slider {
  tbaySlickSlider() {
    jQuery('.flex-control-thumbs').each(function () {
      if ($(this).children().length == 0) {
        return;
      }

      var _config = {};
      _config.vertical = jQuery(this).parent('.woocommerce-product-gallery').data('layout') === 'vertical';
      _config.slidesToShow = jQuery(this).parent('.woocommerce-product-gallery').data('columns');
      _config.infinite = false;
      _config.focusOnSelect = true;
      _config.settings = "unslick";
      _config.prevArrow = '<span class="owl-prev"></span>';
      _config.nextArrow = '<span class="owl-next"></span>';
      _config.rtl = jQuery(this).parent('.woocommerce-product-gallery').data('rtl') === 'yes' && jQuery(this).parent('.woocommerce-product-gallery').data('layout') !== 'vertical';

      if ($(this).is('.style-slide')) {
        _config.responsive = [{
          breakpoint: 1200,
          settings: {
            slidesToShow: 3
          }
        }, {
          breakpoint: 767,
          settings: {
            vertical: false,
            slidesToShow: 4
          }
        }];
      } else {
        _config.responsive = [{
          breakpoint: 1200,
          settings: {
            vertical: false,
            slidesToShow: 3
          }
        }];
      }

      $(this).slick(_config);
    });
  }

}

class Layout {
  tbaySlickLayoutSlide() {
    if (jQuery('.tbay-slider-for').length > 0) {
      var _configfor = {};
      var _confignav = {};
      _configfor.rtl = _confignav.rtl = jQuery('body').hasClass('rtl');
      _configfor.slidesToShow = 1;
      var number_table = 1;

      if (jQuery('.tbay-slider-for').data('number') > 0) {
        _configfor.slidesToShow = jQuery('.tbay-slider-for').data('number');
        number_table = jQuery('.tbay-slider-for').data('number') - 1;
      }

      _configfor.arrows = true;
      _configfor.infinite = true;
      _configfor.slidesToScroll = 1;
      _configfor.prevArrow = '<span class="owl-prev"></span>';
      _configfor.nextArrow = '<span class="owl-next"></span>';
      _configfor.asNavFor = '.tbay-slider-nav';
      _configfor.responsive = [{
        breakpoint: 1025,
        settings: {
          vertical: false,
          slidesToShow: number_table
        }
      }, {
        breakpoint: 480,
        settings: {
          vertical: false,
          slidesToShow: 1
        }
      }];
      _confignav.dots = false;
      _confignav.arrows = true;
      _confignav.centerMode = false;
      _confignav.focusOnSelect = true;
      _confignav.infinite = false;
      _confignav.slidesToShow = 4;
      _confignav.slidesToScroll = 1;
      _confignav.prevArrow = '<span class="owl-prev"></span>';
      _confignav.nextArrow = '<span class="owl-next"></span>';
      _confignav.asNavFor = '.tbay-slider-for';
      jQuery('.tbay-slider-for').slick(_configfor);
      jQuery('.tbay-slider-nav').slick(_confignav);

      if (jQuery('.single-product .tbay-slider-for .slick-slide').length) {
        jQuery('.single-product .tbay-slider-for .slick-slide').zoom();
        jQuery('.single-product .tbay-slider-for .slick-track').addClass('woocommerce-product-gallery__image single-product-main-image');
      }
    }
  }

}

jQuery(document).ready(function () {
  var slider = new Slider();
  var layout = new Layout();
  slider.tbaySlickSlider();
  layout.tbaySlickLayoutSlide();

  if (typeof greenmart_settings !== "undefined" && greenmart_settings.skin_elementor) {
    var carousel = new Carousel();
    carousel.CarouselSlick();
    carousel.getSlickTabs();
  }
});
setTimeout(function () {
  jQuery(window).on('qv_loader_stop', function () {
    var carousel = new Carousel();
    carousel.CarouselSlickQuickView();
  });
  jQuery(document.body).on('tbay_carousel_slick', () => {
    if (typeof greenmart_settings !== "undefined" && greenmart_settings.skin_elementor) {
      var carousel = new Carousel();
      carousel.CarouselSlick();
    }
  });
}, 30);

var CustomSlickHandler = function ($scope, $) {
  var carousel = new Carousel();
  carousel.CarouselSlick();
};

jQuery(window).on('elementor/frontend/init', function () {
  if (typeof greenmart_settings !== "undefined" && greenmart_settings.skin_elementor && Array.isArray(greenmart_settings.elements_ready.slick)) {
    $.each(greenmart_settings.elements_ready.slick, function (index, value) {
      elementorFrontend.hooks.addAction('frontend/element_ready/tbay-' + value + '.default', CustomSlickHandler);
    });
  }
});
