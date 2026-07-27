! function(e) {
  "use strict";
  e(document).ready(function() {
    e("body").scrollspy({
      target: ".header-area",
      offset: 50
    }), e('[data-toggle="tooltip"]').tooltip(), e(".mainmenu").slicknav({
      label: "",
      duration: 500,
      prependTo: "",
      closedSymbol: '<i class="fa fa-angle-right"></i>',
      openedSymbol: '<i class="fa fa-angle-right"></i>',
      appendTo: ".header-area",
      menuButton: ".toggle",
      closeOnClick: "true"
    }), e(".toggle").on("click", function() {
      e(this).toggleClass("active")
    }), e(".t-carousel").owlCarousel({
      loop: !0,
      autoplay: !0,
      smartSpeed: 1e3,
      autoplayTimeout: 5e3,
      dots: !1,
      nav: !1,
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 2
        },
        1000: {
          items: 3
        }
      }
    }), e(".t-c-4").owlCarousel({
      loop: !0,
      autoplay: !0,
      autoplayTimeout: 5e3,
      smartSpeed: 1e3,
      dots: !1,
      nav: !1,
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 1
        },
        1000: {
          items: 2
        }
      }
    }), e(".t-c-5").owlCarousel({
      items: 1,
      loop: !0,
      autoplay: !0,
      autoplayTimeout: 5e3,
      smartSpeed: 1e3,
      dots: !0,
      nav: !1
    }), e(".c-logo-carousel").owlCarousel({
      margin: 30,
      loop: !0,
      autoplay: !0,
      smartSpeed: 1e3,
      autoplayTimeout: 5e3,
      dots: !1,
      nav: !1,
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 3
        },
        1000: {
          items: 5
        }
      }
    }), e(".t6-carousel").owlCarousel({
      loop: !0,
      autoplay: !0,
      smartSpeed: 1e3,
      autoplayTimeout: 5e3,
      dots: !1,
      nav: !0,
      navText: ["<i class='far fa-angle-left'></i>", "<i class='far fa-angle-right'></i>"],
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 2
        },
        1000: {
          items: 3
        }
      }
    }), e(".hero-7").owlCarousel({
      items: 1,
      loop: !0,
      autoplay: !1,
      autoplayTimeout: 5e3,
      smartSpeed: 1e3,
      dots: !1,
      nav: !0,
      navText: ["<i class='far fa-long-arrow-alt-left'></i>", "<i class='far fa-long-arrow-alt-right'></i>"]
    }), e(".t9-content").owlCarousel({
      items: 1,
      loop: !0,
      autoplay: !1,
      autoplayTimeout: 5e3,
      smartSpeed: 1e3,
      dots: !1,
      nav: !0,
      navText: ["<i class='far fa-long-arrow-alt-left'></i>", "<i class='far fa-long-arrow-alt-right'></i>"]
    }), e(".progress-slider").owlCarousel({
      loop: !0,
      autoplay: !1,
      autoplayTimeout: 5e3,
      smartSpeed: 1e3,
      dots: !1,
      nav: !0,
      navText: ["<i class='far fa-angle-left'></i>", "<i class='far fa-angle-right'></i>"],
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 2
        },
        1000: {
          items: 4
        }
      }
    });
    var a = e(".nav-bar");
    e(".prevbtn").on("click", function() {
      a.trigger("next.owl.carousel")
    }), e(".nextbtn").on("click", function() {
      a.trigger("prev.owl.carousel", [300])
    }), e(".counter-number span").counterUp({
      delay: 10,
      time: 2e3
    });
    // new Swiper(".gallery-slider", {
    //   autoplay: !1,
    //   speed: 3e3,
    //   effect: "coverflow",
    //   loop: !0,
    //   centeredSlides: !0,
    //   slidesPerView: 3.5,
    //   coverflow: {
    //     rotate: 0,
    //     stretch: 0,
    //     depth: 250,
    //     modifier: 1,
    //     slideShadows: !1
    //   },
    //   breakpoints: {
    //     0: {
    //       slidesPerView: 1
    //     },
    //     480: {
    //       slidesPerView: 1.5
    //     },
    //     768: {
    //       slidesPerView: 2.5
    //     },
    //     1000: {
    //       slidesPerView: 3.5
    //     }
    //   }
    // }), new Swiper(".hero-6-slider", {
    //   autoplay: !0,
    //   speed: 8e3,
    //   effect: "coverflow",
    //   loop: !0,
    //   centeredSlides: !0,
    //   slidesPerView: 1.1,
    //   coverflow: {
    //     rotate: 0,
    //     stretch: 20,
    //     depth: 250,
    //     modifier: 1,
    //     slideShadows: !1
    //   }
    // });
    e("#mainmenu-area").sticky({
      topSpacing: 0
    }), (new WOW).init({
      mobile: !1
    }), e(".preloader").fadeOut("slow"), e("#mc-form").ajaxChimp({
      url: "http://www.devitfamily.us14.list-manage.com/subscribe/post?u=b2a3f199e321346f8785d48fb&id=d0323b0697",
      callback: function(a) {
        "success" === a.result && e(".subscribe .input-box, .subscribe .bttn-4").fadeOut()
      }
    });
    new SmoothScroll('a[href*="#"]', {
      speed: 1e3
    });
    if ("function" == typeof Typed) new Typed(".typing", {
      strings: ["Work Speed"],
      loop: !0,
      typeSpeed: 100,
      backSpeed: 80
    })
  })
}(jQuery);