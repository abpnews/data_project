(function ($) {
	
	"use strict";

	// Header Type = Fixed
  
  // Acc
    $(document).on("click", ".naccs .menu div", function() {
      var numberIndex = $(this).index();

      if (!$(this).is("active")) {
          $(".naccs .menu div").removeClass("active");
          $(".naccs ul li").removeClass("active");

          $(this).addClass("active");
          $(".naccs ul").find("li:eq(" + numberIndex + ")").addClass("active");

          var listItemHeight = $(".naccs ul")
            .find("li:eq(" + numberIndex + ")")
            .innerHeight();
          $(".naccs ul").height(listItemHeight + "px");
        }
    });


	

	// Menu Dropdown Toggle
  if($('.menu-trigger').length){
    $(".menu-trigger").on('click', function(e) { 
      e.preventDefault();
      $(this).toggleClass('active');
      $('.header-area .nav').slideToggle(200);
    });
  }


	// Page loading animation
	 $(window).on('load', function() {


  });

	

	




})(window.jQuery);