'use strict';

class CarouselModules {
  CarouselSlick($load) {
    var _this = this;

    if (jQuery(".owl-carousel[data-carousel=owl]:visible").length === 0) return;
    jQuery('.owl-carousel[data-carousel=owl]:visible:not(.scroll-init)').each(function () {
      _this._initCarouselSlick(jQuery(this), $load);
    });
    jQuery('.owl-carousel[data-carousel=owl]:visible.scroll-init').waypoint(function () {
      var $this = $($(this)[0].element);

      _this._initCarouselSlick($this, $load);
    }, {
      offset: '100%'
    });
  }

  _initCarouselSlick(_this2, $load) {
    var _this = this;

    if (_this2.hasClass("owl-loaded")) {
      return;
    }

    if (!jQuery.browser.mobile || $(window).width() > 767) {
      _this._getSlickConfigOption(_this2, $load);
    } else if (!_this2.data('uncarouselmobile')) {
      _this._getSlickConfigOption(_this2, $load);
    }
  }

  _getSlickConfigOption(el, $load) {
    var navleft = '<span class="' + el.data('navleft') + '"></span>';
    var navright = '<span class="' + el.data('navright') + '"></span>';
    var config = {
      loop: false,
      nav: el.data('nav'),
      dots: el.data('pagination'),
      items: 4,
      stagePadding: 0,
      navText: [navleft, navright]
    };
    var checkWidth = jQuery(window).width();
    var owl = el;

    if (el.data('items')) {
      config.items = el.data('items');
      var desktop_full = el.data('items');
    }

    if (el.data('large')) {
      var desktop = el.data('large');
    } else {
      var desktop = config.items;
    }

    if (el.data('medium')) {
      var medium = el.data('medium');
    } else {
      var medium = config.items;
    }

    if (el.data('smallmedium')) {
      var smallmedium = el.data('smallmedium');
    } else {
      var smallmedium = config.items;
    }

    if (el.data('extrasmall')) {
      var extrasmall = el.data('extrasmall');
    } else {
      var extrasmall = 2;
    }

    if (el.data('verysmall')) {
      var verysmall = el.data('verysmall');
    } else {
      var verysmall = 1;
    }

    config.responsive = {
      0: {
        items: extrasmall
      },
      480: {
        items: smallmedium
      },
      768: {
        items: medium
      },
      1280: {
        items: desktop
      },
      1600: {
        items: desktop_full
      }
    };

    if ($('html').attr('dir') == 'rtl') {
      config.rtl = true;
    }

    if (el.data('loop')) {
      config.loop = el.data('loop');
    }

    if (el.data('auto')) {
      config.autoplay = el.data('auto');
    }

    if (el.data('autospeed')) {
      config.autoplaySpeed = el.data('autospeed');
    }

    el.owlCarousel(config);

    if (!owl.data('uncarouselmobile') || checkWidth >= 767) {
      el.owlCarousel(config);

      if ($load) {
        owl.trigger('refresh.owl.carousel');
      }
    } else {
      el.trigger('destroy.owl.carousel').removeClass('owl-loaded');
      el.find('.owl-stage-outre').children().unwrap();
      el.find('.item').children().unwrap();
    }

    var viewport = jQuery(window).width();
    var itemCount = jQuery(".owl-item", el).length;

    if (viewport >= 1600 && itemCount <= desktop_full || viewport >= 1280 && viewport < 1600 && itemCount <= desktop || viewport >= 980 && viewport < 1280 && itemCount <= medium || viewport >= 768 && viewport < 980 && itemCount <= smallmedium || viewport >= 320 && viewport < 768 && itemCount <= extrasmall || viewport < 320 && itemCount <= verysmall) {
      el.find('.owl-prev, .owl-next').hide();
    }
  }

  getSlickTabs() {
    var _this = this;

    $('ul.nav-tabs li a').on('shown.bs.tab', function (event) {
      var carouselItemTab = $($(event.target).attr("href")).find(".owl-carousel[data-carousel=owl]:visible");
      var carouselItemDestroy = $($(event.relatedTarget).attr("href")).find(".owl-carousel[data-carousel=owl]");

      if (carouselItemDestroy.hasClass("owl-loaded")) {
        carouselItemDestroy.trigger('destroy.owl.carousel').removeClass('owl-loaded');
      }

      if (!carouselItemTab.hasClass("owl-loaded")) {
        _this.CarouselSlick(false);
      }
    });
  }

}

jQuery(window).on('load', function () {
  var carouselmd = new CarouselModules();
  carouselmd.CarouselSlick(true);
});
jQuery(document).ready(function () {
  var carouselmd = new CarouselModules();
  carouselmd.CarouselSlick(false);
  carouselmd.getSlickTabs();
});
setTimeout(function () {
  jQuery(document.body).on('tbay_carousel_slick', () => {
    var carouselmd = new CarouselModules();
    carouselmd.CarouselSlick(false);
  });
}, 2000);
