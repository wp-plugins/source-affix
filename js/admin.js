(function ( $ ) {
	"use strict";

	$(function () {

		if($('#list-source-link').length > 0 ){
      //link stuff

      // Remove button
      $('body').on('click','.btn-remove-source-link',function(e){
        e.preventDefault();
        var confirmation = confirm('Are you sure?');
        if( ! confirmation ){
          return false;
        }
        $(this).parent().css('border','1px red solid').remove();
      });

      // Move button
      $('body').on('click','.btn-move-source-link',function(e){
        e.preventDefault();
      });

      // Add Button
      $('#btn-add-source-link').on('click',function(e){
        e.preventDefault();
        var $li = $('<li></li>');
        $li.append('<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>');
        $li.append('<input type="text" name="link_title[]" value=""  class="regular-text1 code" placeholder="Enter title" />');
        $li.append('<input type="text" name="link_url[]" value=""  class="regular-text code" placeholder="Enter full URL" />');
        $li.append('<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>');
        $li.appendTo('#list-source-link');

      });

      // Make sortable list
      $('#list-source-link').sortable();


    }

	});

}(jQuery));
