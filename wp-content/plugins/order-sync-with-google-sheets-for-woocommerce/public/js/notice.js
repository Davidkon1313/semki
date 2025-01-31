(function ($) {

  $(document).on("click", ".osgsw_notice_dismiss1", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent this click from being propagated to the document
    $(".osgsw_list_option1").toggle();
  });

  $(document).on("click", ".osgsw_notice_dismiss2", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent this click from being propagated to the document
    $(".osgsw_list_option2").toggle();
  });


  $(document).on("click", function (event) {
    if (
      !$(event.target).closest(".osgsw_list_option1").length &&
      !$(event.target).is(".osgsw_notice_dismiss1")
    ) {
      $(".osgsw_list_option1").hide();
    }
    if (
      !$(event.target).closest(".osgsw_list_option2").length &&
      !$(event.target).is(".osgsw_notice_dismiss2")
    ) {
      $(".osgsw_list_option2").hide();
    }
  });



  $(document).on("click", ".osgsw_remove_text_dec", function (e) {
    var self = $(this);
    e.preventDefault();
    $.ajax({
      url: osgsw_notice_data.ajax_url,
      type: "POST",
      data: {
        action: "ossgw_appscript_improved",
        nonce: osgsw_notice_data.nonce,
      },
      beforeSend: function (response) {
        self.html("Waiting....");
      },
      complete: function (response) {
        self.html("Waiting....");
      },
      success: function (response) {
        if (response.url) {
          window.location.href = response.url;
        }
      },
    });
  });
  $(document).on("click", ".osgsw_skip_next_time", function (e) {
    var self = $(this);
    e.preventDefault();
    $.ajax({
      url: osgsw_notice_data.ajax_url,
      type: "POST",
      data: {
        action: "ossgw_notice_skip",
        nonce: osgsw_notice_data.nonce,
      },
      beforeSend: function (response) {
        self.html("Skipping...");
      },
      complete: function (response) {
        self.html("Not now, skip");
      },
      success: function (response) {
        self.html("Not now, skip");
        $(".osgsw_list_option1").hide();
        $(".osgsw_appscript_notice31").fadeOut(300);
        $(".osgsw_appscript_notice32").show();
      },
    });
  });

  $(document).on("click", ".osgsw_dismiss_notice", function (e) {
    e.preventDefault();
    var self = $(this);
    $.ajax({
      url: osgsw_notice_data.ajax_url,
      type: "POST",
      data: {
        action: "ossgw_already_updated",
        nonce: osgsw_notice_data.nonce,
      },
      beforeSend: function (response) {
        self.html("Dismissing...");
      },
      complete: function (response) {
        self.html("Dismiss, already updated");
      },
      success: function (response) {
        self.html("Dismiss, already updated");
        $(".osgsw_appscript_notice31").fadeOut(300);
        $(".osgsw_appscript_notice32").hide();
      },
    });
  });



  $(document).on("click", ".osgsw_save_close1", function (e) {
    e.preventDefault();
    var self = $(this);
    $.ajax({
      url: osgsw_notice_data.ajax_url,
      type: "POST",
      data: {
        action: "ossgw_already_updated_trigger",
        nonce: osgsw_notice_data.nonce,
      },
      beforeSend: function (response) {
        self.html("Waiting...");
      },
      complete: function (response) {
        self.html("Waiting...");
      },
      success: function (response) {
        location.reload();
      },
    });
  });

  $(document).on("click", ".osgsw_appscript_notice32", function (e) {
    $(this).hide();
    $(".osgsw_appscript_notice31").fadeIn(300);
  });

  $(document).on("click", ".osgsw_appscript_notice34", function (e) {
    $(this).hide();
    $(".osgsw_appscript_notice33").fadeIn(300);
  });
  $(document).on("ready", function (e) {
    //changing the banner position
    //license notice
    const osgswPage = document.querySelector(".osgsw-wrapper");
    const licenseNotice = document.querySelector(".osgsw-license-notice");
    const appScriptNotice = document.querySelector(".osgsw_appscript_notice3");
    const ratingNotice = document.querySelector(".osgs-rating-banner");
    const upgradeNotice = document.querySelector(".osgs-upgrade-banner");
    const influencerNotice = document.querySelector(".osgs-influencer-banner");
    if (osgswPage) {
      var wpBody = document.querySelector("#wpcontent #wpbody-content");
    } else {
      var wpBody = document.querySelector("#wpcontent .wrap");
      if (wpBody && (ratingNotice || upgradeNotice || influencerNotice)) {
        wpBody.style.margin = "40px 20px 0 2px";
      }
    }
    const alreadyRated = localStorage.getItem("already_rated");
    const upgradeClose = localStorage.getItem("upgrade_button");
    const influencerClose = localStorage.getItem("influencer_button");
    if (licenseNotice) {
      // Remove banner from its current position
      licenseNotice.remove();
      wpBody.insertBefore(licenseNotice, wpBody.firstChild);
    }
    // appscript notice
    if (appScriptNotice) {
      appScriptNotice.remove();
      appScriptNotice.style.display = "block";
      if (licenseNotice) {
        wpBody.insertBefore(appScriptNotice, licenseNotice.nextSibling);
      } else {
        wpBody.insertBefore(appScriptNotice, wpBody.firstChild);
      }
    }

    if (ratingNotice && !alreadyRated) {
      ratingNotice.remove();
      ratingNotice.style.display = "flex";
      if (appScriptNotice) {
        wpBody.insertBefore(ratingNotice, appScriptNotice.nextSibling);
      } else {
        wpBody.insertBefore(ratingNotice, wpBody.firstChild);
      }
    }

    if (upgradeNotice && !upgradeClose) {
      // upgradeNotice.remove();
      upgradeNotice.style.display = "flex";
      if (appScriptNotice) {
        wpBody.insertBefore(upgradeNotice, appScriptNotice.nextSibling);
      } else {
        wpBody.insertBefore(upgradeNotice, wpBody.firstChild);
      }
    }

    if (influencerNotice && !influencerClose) {
      // influencerNotice.remove();
      influencerNotice.style.display = "flex";
      if (appScriptNotice) {
        wpBody.insertBefore(influencerNotice, appScriptNotice.nextSibling);
      } else {
        wpBody.insertBefore(influencerNotice, wpBody.firstChild);
      }
    }

    // Rating Star

    const grayIcons = document.querySelectorAll(".osgs-yellow-icon");

    grayIcons.forEach((icon, index) => {
      icon.addEventListener("mouseover", () => {
        for (let i = index + 1; i < grayIcons.length; i++) {
          grayIcons[i].classList.remove("osgs-yellow-icon");
          grayIcons[i].classList.add("osgs-gray-icon");
        }

        // Add 'osgs-orange-icon' class to icons on the left side
        for (let i = 0; i <= index; i++) {
          grayIcons[i].classList.add("osgs-orange-icon");
        }

        icon.addEventListener("mouseout", () => {
          for (let i = 0; i <= index; i++) {
            grayIcons[i].classList.remove("osgs-orange-icon");
            grayIcons[i].classList.remove("osgs-gray-icon");
          }
          $(".rating-container").each(function () {
            $(this).children().removeClass("osgs-gray-icon");
            $(this).children().addClass("osgs-yellow-icon");
          });
        });
        // for (let i = 0; i <= index; i++) {
        //   grayIcons[i].classList.add("osgs-yellow-icon");
        // }
      });
    });

    //rating popup js
    $(document).on("click", ".osgs-rating-close", function (e) {
      e.preventDefault();
      var $this = $(this);
      $this.addClass("osgsw_second_close_button");
      $(".osgsw_popup-container").css("display", "flex");
      $(".osgsw_popup-content").css("display", "block");
      $(".osgsw_first_section2").css({
        display: "flex",
        "flex-direction": "column",
        "width": "97.5%"
      });
    });

    // dropdown selection of days
    $(".selected-option").on("click", function () {
      $(this).siblings(".options").toggle();
    });

    $(".options li").on("click", function () {
      var selectedValue = $(this).data("value");
      var selectedText = $(this).text();
      // Update the data-days attribute of the selected-option div
      $(".selected-option").data("days", selectedValue).text(selectedText);

      $(".options").hide();
      // You can perform any necessary actions with the selected value here
    });

    $(document).on("click", function (e) {
      var container = $(".osgsw-days-dropdown");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $(".options").hide();
      }
    });

    //upgrade popup close
    $(document).on("click", ".osgs-upgrade-close", function (e) {
      e.preventDefault();
      localStorage.setItem("upgrade_button", true);
      $(this).parent().fadeOut();
    });

    //upgrade popup close
    $(document).on("click", ".osgs-influencer-close", function (e) {
      e.preventDefault();
      localStorage.setItem("influencer_button", true);
      $(this).parent().fadeOut();
    });

    //rating star onClick
    $(".rating-container .osgs-yellow-icon").on("click", function () {
      $(".rating-container .osgs-orange-icon").removeClass("osgs-orange-icon");
      $(".rating-container .osgs-gray-icon").removeClass("osgs-gray-icon");
      $(".rating-container").each(function () {
        $(this).children().addClass("osgs-yellow-icon");
      });
    });

    // When the 5th (last) span is clicked
    $(".rating-container .osgs-yellow-icon:last-child").on(
      "click",
      function () {
        // Redirect to a particular hyperlink safely
        const link =
          "https://wordpress.org/support/plugin/order-sync-with-google-sheets-for-woocommerce/reviews/?filter=5";
        window.open(link, "_blank");
      }
    );

    // When the first 4 spans are clicked
    $(".rating-container .osgs-yellow-icon:not(:last-child)").on(
      "click",
      function () {
        const supportLink = "https://wppool.dev/contact/";
        window.open(supportLink, "_blank");
      }
    );

    $(document).on("click", ".osgs-already-rated", function (e) {
      e.preventDefault();
      localStorage.setItem("already_rated", true);
      $(".osgs-rating-banner").fadeOut();
    });

    //popup js rating
    $(document).on("click", ".osgsw_close_button", function (e) {
      e.preventDefault();
      var $this = $(this);
      console.log("Close button clicked");
      $this.removeClass("osgsw_close_button");
      $this.addClass("osgsw_second_close_button");
      $(".osgsw_first_section").fadeOut();
      $(".osgsw_popup-content").fadeOut();
      window.location.reload();
    });
    $(document).on("click", ".osgsw_submit_button2", function (e) {
      e.preventDefault();
      var $this = $(this);
      var values = $(".selected-option").data("days");
      var data = {
        action: "osgsw_popup_handle",
        nonce: osgsw_notice_data.nonce,
        value: values,
      };
      console.log(data);
      $.ajax({
        type: "post",
        url: osgsw_notice_data.ajax_url,
        data: data,
        beforeSend: function (response) {
          $this.html("Loading...");
        },
        complete: function (response) {
          $this.html("Ok");
        },
        success: function (response) {
          console.log(response);
          if (1 == response.data.days_count) {
            localStorage.setItem("already_rated", true);
          }
          window.location.reload();
        },
      });
    });
  });
})(jQuery);
