(function ($) {
    $(document).ready(function () {
        // Flag to track if an AJAX request is in progress
        let ajaxInProgress = false;

        // Set up beforeunload warning
        $(window).on('beforeunload', function () {
            if (ajaxInProgress) {
                return 'Changes you made may not be saved. Are you sure you want to leave?';
            }
        });

        /**
         * Show a message to the user
         * @param {string} message - The message to display
         * @param {string} type - success or error
         */
        function showMessage(message, type = 'success') {
            // Hide all messages first
            $('.pcmg-message').hide();

            // Show appropriate message
            const messageElement = type === 'success' ? $('#pcmg-success-message') : $('#pcmg-error-message');
            messageElement.html(message).fadeIn();

            // Auto hide after 5 seconds
            setTimeout(function () {
                messageElement.fadeOut();
            }, 5000);

            // Scroll to message
            $('html, body').animate({
                scrollTop: messageElement.offset().top - 50
            }, 500);
        }

        /**
         * Parse AJAX response and handle errors consistently
         * @param {object} response - The AJAX response
         * @param {function} successCallback - Function to call on success
         * @returns {boolean} - Whether the response was successful
         */
        function parseResponse(response, successCallback) {
            // Check if response is valid JSON
            if (typeof response !== 'object') {
                showMessage(pcmg_ajax.error_messages.invalid_response, 'error');
                return false;
            }

            // Check for success flag
            if (response.success) {
                if (typeof successCallback === 'function') {
                    successCallback(response);
                }
                return true;
            } else {
                // Handle errors
                let errorMessage = pcmg_ajax.error_messages.general;

                if (response.data) {
                    errorMessage = response.data;
                }

                showMessage(errorMessage, 'error');
                return false;
            }
        }

        /**
         * Handle AJAX errors consistently
         * @param {object} request - The failed request object
         * @param {string} action - The action that was being performed
         */
        function handleAjaxError(request, action) {
            let errorMessage = pcmg_ajax.error_messages[action] || pcmg_ajax.error_messages.general;

            if (request.responseJSON && request.responseJSON.data) {
                errorMessage = request.responseJSON.data;
            }

            showMessage(errorMessage, 'error');
        }

        /**
         * Show the loading overlay
         */
        function showLoadingOverlay() {
            $('.pcmg-loading-overlay').addClass('active');
            ajaxInProgress = true;
        }

        /**
         * Hide the loading overlay
         */
        function hideLoadingOverlay() {
            $('.pcmg-loading-overlay').removeClass('active');
            ajaxInProgress = false;
        }

        /**
         * Show loading spinner
         */
        function showLoading(element) {
            $(element).show();
        }

        /**
         * Hide loading spinner
         */
        function hideLoading(element) {
            $(element).hide();
        }

        /* Select All Checkbox */
        $('#pcmg-select-all').on('change', function () {
            const isChecked = $(this).prop('checked');
            $('.pcmg-menu-checkbox').prop('checked', isChecked);

            // Highlight selected rows
            if (isChecked) {
                $('.pcmg-menus-table tbody tr').addClass('selected');
            } else {
                $('.pcmg-menus-table tbody tr').removeClass('selected');
            }
        });

        /* Individual Checkbox */
        $(document).on('change', '.pcmg-menu-checkbox', function () {
            const $row = $(this).closest('tr');

            // Highlight selected row
            if ($(this).prop('checked')) {
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }

            // Handle "Select All" checkbox state
            const totalCheckboxes = $('.pcmg-menu-checkbox').length;
            const checkedCheckboxes = $('.pcmg-menu-checkbox:checked').length;

            $('#pcmg-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        /* Bulk Action Apply Button */
        $('#pcmg-bulk-apply').on('click', function () {
            const action = $('#pcmg-bulk-action').val();
            const $selectedCheckboxes = $('.pcmg-menu-checkbox:checked');

            if (!action) {
                showMessage(pcmg_ajax.error_messages.no_action, 'error');
                return;
            }

            if ($selectedCheckboxes.length === 0) {
                showMessage(pcmg_ajax.error_messages.no_selection, 'error');
                return;
            }

            // Handle different bulk actions
            if (action === 'delete') {
                bulkDeleteMenus($selectedCheckboxes);
            }
        });

        /**
         * Bulk delete the selected menus
         * @param {jQuery} $checkboxes - The selected checkboxes jQuery object
         */
        function bulkDeleteMenus($checkboxes) {
            // Confirm deletion
            if (!confirm(pcmg_ajax.confirm_messages.bulk_delete)) {
                return;
            }

            // Get the menu IDs from the checkboxes
            const menuIds = [];
            $checkboxes.each(function () {
                menuIds.push($(this).val());
            });

            // Show loading state
            const $bulkLoading = $('#pcmg-bulk-loading');
            $('#pcmg-bulk-apply').prop('disabled', true);
            $bulkLoading.show();

            // Show loading overlay
            showLoadingOverlay();

            // Make the AJAX request
            $.ajax({
                url: pcmg_ajax.ajaxurl,
                type: 'post',
                data: {
                    action: 'bulk_delete_menus',
                    menu_ids: menuIds,
                    nonce_ajax: pcmg_ajax.nonce
                },
                dataType: 'json',
                beforeSend: function () {
                    ajaxInProgress = true;
                },
                success: function (response) {
                    // Hide loading overlay
                    hideLoadingOverlay();

                    if (parseResponse(response, function () {
                        // Display success message
                        showMessage(response.data, 'success');

                        // Remove the deleted rows with animation
                        $checkboxes.each(function () {
                            const $row = $(this).closest('tr');

                            $row.fadeOut(400, function () {
                                $(this).remove();
                            });
                        });

                        // Check if there are any menus left
                        setTimeout(function () {
                            if ($('.pcmg-menus-table tbody tr:visible').length === 0) {
                                location.reload(); // Reload to update the UI correctly
                            }
                        }, 500);
                    })) {
                        // Success handling is done in parseResponse callback
                    }
                },
                error: function (request, status, error) {
                    // Hide loading overlay
                    hideLoadingOverlay();
                    handleAjaxError(request, 'bulk_delete');
                },
                complete: function () {
                    // Reset button state
                    $('#pcmg-bulk-apply').prop('disabled', false);
                    $bulkLoading.hide();

                    // Reset select all checkbox
                    $('#pcmg-select-all').prop('checked', false);

                    // Indicate AJAX is no longer in progress
                    ajaxInProgress = false;
                }
            });
        }

        /* Delete Menu */
        $(document).on('click', '.delete-menu', function () {
            if (!confirm(pcmg_ajax.confirm_messages.delete)) {
                return;
            }

            const $button = $(this);
            const menuId = $button.data('menu-id');
            const $spinner = $('#loading-' + menuId);
            const $row = $button.closest('tr');

            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();

            // Show loading overlay
            showLoadingOverlay();

            $.ajax({
                url: pcmg_ajax.ajaxurl,
                type: "post",
                data: {
                    action: "delete_menu",
                    menu_id: menuId,
                    nonce_ajax: pcmg_ajax.nonce
                },
                dataType: "json",
                beforeSend: function () {
                    ajaxInProgress = true;
                },
                success: function (response) {
                    // Hide loading overlay
                    hideLoadingOverlay();

                    if (parseResponse(response, function () {
                        // Display success message
                        showMessage(response.data, 'success');

                        // Remove the row with animation
                        $row.fadeOut(400, function () {
                            $(this).remove();

                            // Check if there are any menus left
                            if ($('.pcmg-menus-table tbody tr').length === 0) {
                                location.reload(); // Reload to update the UI correctly
                            }
                        });
                    })) {
                        // Success handling is done in parseResponse callback
                    }
                },
                error: function (request, status, error) {
                    // Hide loading overlay
                    hideLoadingOverlay();

                    handleAjaxError(request, 'delete');

                    // Reset button state
                    $button.prop('disabled', false);
                    $spinner.hide();
                },
                complete: function () {
                    ajaxInProgress = false;
                }
            });
        });

        /* Update Menu */
        $(document).on('click', '.update-menu', function () {
            if (!confirm(pcmg_ajax.confirm_messages.update)) {
                return;
            }

            const $button = $(this);
            const menuId = $button.data('menu-id');
            const $spinner = $('#loading-' + menuId);

            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();

            // Show the loading overlay
            showLoadingOverlay();

            $.ajax({
                url: pcmg_ajax.ajaxurl,
                type: "post",
                data: {
                    action: "update_menu",
                    menu_id: menuId,
                    nonce_ajax: pcmg_ajax.nonce
                },
                dataType: "json",
                beforeSend: function () {
                    ajaxInProgress = true;
                },
                success: function (response) {
                    // Hide loading overlay
                    hideLoadingOverlay();

                    if (parseResponse(response, function () {
                        // Display success message
                        showMessage(response.data, 'success');

                        // Reload the page after a short delay
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    })) {
                        // Success handling is done in parseResponse callback
                    }
                },
                error: function (request, status, error) {
                    // Hide loading overlay
                    hideLoadingOverlay();
                    handleAjaxError(request, 'update');

                    // Reset button state
                    $button.prop('disabled', false);
                    $spinner.hide();
                },
                complete: function () {
                    ajaxInProgress = false;
                }
            });
        });

        /* Generate Menu Form Submission */
        $('#menu-generator-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $button = $('#generate-menu');
            const $spinner = $('#loading-new');
            const menuName = $('#menu-name').val();
            const skipEmpty = $('#skip-empty').is(':checked');
            const menuDepth = $('#menu-depth').val();

            // Validate input
            if (!menuName) {
                showMessage(pcmg_ajax.error_messages.empty_name, 'error');
                return;
            }

            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();

            // Show loading overlay
            showLoadingOverlay();

            // Allow for custom form data through filter
            const formData = {
                action: "generate_menu",
                menu_name: menuName,
                skip_empty: skipEmpty,
                menu_depth: menuDepth,
                nonce_ajax: pcmg_ajax.nonce
            };

            // Apply custom form data from plugins
            if (typeof pcmg_custom_form_data === 'function') {
                const customData = pcmg_custom_form_data($form);
                if (customData && typeof customData === 'object') {
                    $.extend(formData, customData);
                }
            }

            $.ajax({
                url: pcmg_ajax.ajaxurl,
                type: "post",
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    ajaxInProgress = true;
                },
                success: function (response) {
                    // Hide loading overlay
                    hideLoadingOverlay();

                    if (parseResponse(response, function () {
                        // Display success message
                        showMessage(response.data, 'success');

                        // Clear form
                        $form[0].reset();

                        // Reload the page after a short delay
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    })) {
                        // Success handling is done in parseResponse callback
                    }
                },
                error: function (request, status, error) {
                    // Hide loading overlay
                    hideLoadingOverlay();
                    handleAjaxError(request, 'generate');

                    // Reset button state
                    $button.prop('disabled', false);
                    $spinner.hide();
                },
                complete: function () {
                    ajaxInProgress = false;
                }
            });
        });

        // Add entrance animations on page load
        function initPageAnimations() {
            $('.pcmg-card').each(function (index) {
                $(this).css({
                    'opacity': 0,
                    'transform': 'translateY(20px)'
                });

                setTimeout(() => {
                    $(this).css({
                        'transition': 'opacity 0.5s ease, transform 0.5s ease',
                        'opacity': 1,
                        'transform': 'translateY(0)'
                    });
                }, 100 * index);
            });

            $('.pcmg-stats-card').each(function (index) {
                $(this).css({
                    'opacity': 0,
                    'transform': 'scale(0.95)'
                });

                setTimeout(() => {
                    $(this).css({
                        'transition': 'opacity 0.4s ease, transform 0.4s ease',
                        'opacity': 1,
                        'transform': 'scale(1)'
                    });
                }, 150 * index);
            });
        }

        // Initialize animations
        initPageAnimations();

        // Add button hover effects
        $('.pcmg-action-button').on('mouseenter', function () {
            $(this).addClass('hover');
        }).on('mouseleave', function () {
            $(this).removeClass('hover');
        });
    });
})(jQuery)