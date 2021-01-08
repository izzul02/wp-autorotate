( function( $ ) {
	$( document ).ready( function() {
		'use strict';

        /***
         * CLEAR LOG
         */
        $(document).on('click', '#clear-log', function(event) {
            event.preventDefault();

            if (confirm('Clear Logs?')) {               
                $.ajax({
                    method: 'POST',
                    url: arpParam.ajaxurl,
                    data: {
                        action: 'clear_log',
                    },
                    success: function(response) {
                        if ( response.is_success === true ) window.location.reload();
                    }
                });
            }
        });

        /***
         * DELETE
         */
		$(document).on('click', '#arp-delete', function(event) {
            event.preventDefault();

            if (confirm('Delete Ruleset?')) {
                var id = $(this).data('id');
                
                if ( id ) {
                    $.ajax({
                        method: 'POST',
                        url: arpParam.ajaxurl,
                        data: {
                            action: 'delete_item',
                            delete_item_id: id,
                        },
                        success: function(response) {
                            if ( response.is_success === true ) window.location.reload();
                        }
                    });
                }
            }
        });

        /***
         * Pause
         */
        $(document).on('click', '#arp-pause', function(event) {
            event.preventDefault();

            if (confirm('Pause Ruleset?')) {
                var id = $(this).data('id');
                
                if ( id ) {
                    $.ajax({
                        method: 'POST',
                        url: arpParam.ajaxurl,
                        data: {
                            action: 'pause_item',
                            ruleset_id: id,
                        },
                        success: function(response) {
                            if ( response.is_success === true ) window.location.reload();
                        }
                    });
                }
            }
        });

        /***
         * Pause
         */
        $(document).on('click', '#arp-resume', function(event) {
            event.preventDefault();

            if (confirm('Resume Ruleset?')) {
                var id = $(this).data('id');
                
                if ( id ) {
                    $.ajax({
                        method: 'POST',
                        url: arpParam.ajaxurl,
                        data: {
                            action: 'resume_item',
                            ruleset_id: id,
                        },
                        success: function(response) {
                            if ( response.is_success === true ) window.location.reload();
                        }
                    });
                }
            }
        });

        /***
         * RUN RULESET
         */
        $(document).on('click', '#arp-run', function(event) {
            event.preventDefault();

            var $this = $(this),
                ruleset_id = $(this).data('id'),
                item = $(this).data('item'),
                schedule = $(this).data('schedule'),
                keyword = $(this).data('keyword'),
                tag_id = $(this).data('tag-id'),
                category_id = $(this).data('category-id'),
                post_age = $(this).data('post-age');

            $this.prop('disabled', true);
            
            $.ajax({
                method: 'POST',
                url: arpParam.ajaxurl,
                data: {
                    action: 'update_item',
                    ruleset_id: ruleset_id,
                    item: item,
                    schedule: schedule,
                    keyword: keyword,
                    tag_id: tag_id,
                    category_id: category_id,
                    post_age: post_age,
                },
                success: function(response) {
                    $this.prop('disabled', false);
                    alert('Success!');
                }
            });
        });

	} );
} )( jQuery );