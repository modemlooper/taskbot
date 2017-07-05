window.TaskBot = {};
	( function( window, $, that ) {

		// Constructor.
		that.init = function() {
			that.cache();
			that.bindEvents();
		}

		// Cache all the things.
		that.cache = function() {
			that.$c = {
				window: $( window ),
				body: $( 'body' ),
			};
		}

		// Combine all events.
		that.bindEvents = function() {

			// Run show/hide of field options on load.
			that.taskSelectSet();

			// show/hide meta boxes based on select otpion
			that.$c.body.on( 'change', '#_taskbot_task', function() {

                var taskSelect = $(this).val();

                $.each( taskbot, function( item ) {
                    $( '#' + item ).hide();
                    if ( taskSelect === item ) {
                        that.metaShowHide( $( '#' + item ) );
                    }
                });

			});

        }

		// Shows field options based on type select.
		that.taskSelectSet = function() {

            var taskSelect = $('#_taskbot_task').val();

            $.each( taskbot, function( item ) {
                $( '#' + item ).hide();
                if ( taskSelect === item ) {
                    that.metaShowHide( $( '#' + item ) );
                }
            });
		}

        // Function to handle which items should be showing/hiding
        that.metaShowHide = function(showem) {
            showem.slideDown('fast');
        }

		// Engage!
		$( that.init );

	})( window, jQuery, window.TaskBot );
