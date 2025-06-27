$(() => {
    'use strict';

    // Get URLs from data attributes
    const container = $('#api-settings-container');
    const uploadServiceAccountUrl = container.data('upload-service-account-url');
    const removeServiceAccountUrl = container.data('remove-service-account-url');
    const sendNotificationUrl = container.data('send-notification-url');
    const deviceTokensStatsUrl = container.data('device-tokens-stats-url');

    // Edit API key - enable the field for editing
    $(document).on('click', '#edit-api-key', function(e) {
        e.preventDefault();

        const apiKeyInput = $('#api-key-input');
        const editButton = $(this);
        const copyButton = $('#copy-api-key');

        // Enable the input field
        apiKeyInput.prop('readonly', false).focus();

        // Hide edit button and show copy button
        editButton.hide();
        copyButton.show();

        // Show success message
        Botble.showSuccess(window.trans.api.api_key_edit_enabled || 'API key field is now editable.');
    });

    // Generate random API key
    $('#generate-api-key').on('click', function(e) {
        e.preventDefault();

        const apiKeyInput = $('#api-key-input');
        const editButton = $('#edit-api-key');
        const copyButton = $('#copy-api-key');
        const newApiKey = generateRandomApiKey();

        // Enable the input field and set new value
        apiKeyInput.prop('readonly', false).val(newApiKey);

        // Update button visibility
        editButton.hide();
        copyButton.show();

        // Show success message
        Botble.showSuccess(window.trans.api.api_key_generated || 'API key generated successfully!');

        // Update examples with new key
        updateExamplesWithApiKey(newApiKey);
    });

    // The copy functionality is now handled by the <x-core::copy> component

    // Update examples when API key changes
    $('#api-key-input').on('input', function() {
        const apiKey = $(this).val() || (window.trans.api.your_api_key_here || 'your-api-key-here');
        updateExamplesWithApiKey(apiKey);

        // Show/hide copy button based on whether there's a value
        const copyButton = $('#copy-api-key');
        if (apiKey && apiKey !== (window.trans.api.your_api_key_here || 'your-api-key-here')) {
            copyButton.show();
        } else {
            copyButton.hide();
        }
    });

    // Initialize examples on page load
    const currentApiKey = $('#api-key-input').val() || (window.trans.api.your_api_key_here || 'your-api-key-here');
    updateExamplesWithApiKey(currentApiKey);

    // Handle service account file upload
    $('#upload-service-account-btn').on('click', function(e) {
        e.preventDefault();
        $('#service-account-file-input').click();
    });

    $('#service-account-file-input').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (file.type !== 'application/json' && !file.name.toLowerCase().endsWith('.json')) {
                Botble.showError(window.trans.api.invalid_json_file || 'Please select a valid JSON file.');
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                Botble.showError(window.trans.api.file_size_too_large || 'File size must be less than 2MB.');
                return;
            }

            uploadServiceAccountFile(file);
        }
    });

    // Handle service account file removal
    $('#remove-service-account-btn').on('click', function(e) {
        e.preventDefault();

        if (confirm(window.trans.api.confirm_remove_service_account || 'Are you sure you want to remove the service account file?')) {
            removeServiceAccountFile();
        }
    });

    /**
     * Upload service account file
     * @param {File} file
     */
    function uploadServiceAccountFile(file) {
        const formData = new FormData();
        formData.append('service_account_file', file);
        formData.append('_token', $('input[name="_token"]').val());

        // Show upload progress
        const uploadBtn = $('#upload-service-account-btn');
        const progressDiv = $('#upload-progress');
        const progressBar = progressDiv.find('.progress-bar');

        uploadBtn.prop('disabled', true);
        progressDiv.show();
        progressBar.css('width', '0%');

        $.ajax({
            url: uploadServiceAccountUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        progressBar.css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.error === false) {
                    // Update the input field
                    $('#fcm-service-account-input').val(response.data.path);

                    // Update status display
                    updateServiceAccountStatus(response.data.path, response.data.filename);

                    // Show success message
                    Botble.showSuccess(window.trans.api.file_uploaded_successfully || 'File uploaded successfully!');

                    // Refresh the page to update the form state
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Botble.showError(response.message || (window.trans.api.file_upload_error || 'Failed to upload file'));
                }
            },
            error: function(xhr) {
                let errorMessage = window.trans.api.file_upload_error || 'Failed to upload service account file';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Botble.showError(errorMessage);
            },
            complete: function() {
                uploadBtn.prop('disabled', false);
                progressDiv.hide();
                progressBar.css('width', '0%');
                // Clear the file input
                $('#service-account-file-input').val('');
            }
        });
    }

    /**
     * Remove service account file
     */
    function removeServiceAccountFile() {
        const removeBtn = $('#remove-service-account-btn');
        const originalHtml = removeBtn.html();

        removeBtn.prop('disabled', true).html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6l0 -3" /><path d="M16.25 7.75l2.15 -2.15" /><path d="M18 12l3 0" /><path d="M16.25 16.25l2.15 2.15" /><path d="M12 18l0 3" /><path d="M7.75 16.25l-2.15 2.15" /><path d="M6 12l-3 0" /><path d="M7.75 7.75l-2.15 -2.15" /></svg>');

        $.ajax({
            url: removeServiceAccountUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.error === false) {
                    // Clear the input field
                    $('#fcm-service-account-input').val('');

                    // Update status display
                    updateServiceAccountStatus('', '');

                    // Show success message
                    Botble.showSuccess(window.trans.api.file_removed_successfully || 'File removed successfully!');

                    // Refresh the page to update the form state
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Botble.showError(response.message || (window.trans.api.file_remove_error || 'Failed to remove file'));
                }
            },
            error: function(xhr) {
                let errorMessage = window.trans.api.file_remove_error || 'Failed to remove service account file';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Botble.showError(errorMessage);
            },
            complete: function() {
                removeBtn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    /**
     * Update service account status display
     * @param {string} path
     * @param {string} filename
     */
    function updateServiceAccountStatus(path, filename) {
        const statusDiv = $('#service-account-status');

        if (path && filename) {
            statusDiv.html(`
                <small class="text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 15l2 2l4 -4" /></svg>
                    ${window.trans.api.service_account_file_label || 'Service account file:'} <strong>${filename}</strong>
                    <span class="text-muted">${window.trans.api.just_uploaded || '(Just uploaded)'}</span>
                </small>
            `);
        } else {
            statusDiv.html(`
                <small class="text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
                    ${window.trans.api.service_account_not_uploaded || 'Service account file is <strong>not uploaded</strong>. Please upload your service account JSON file.'}
                </small>
            `);
        }
    }

    // Send push notification
    $('#send-notification-btn').on('click', function(e) {
        e.preventDefault();

        const submitBtn = $(this);
        const resultDiv = $('#notification-result');

        // Validate required fields
        const title = $('#notification-title').val().trim();
        const message = $('#notification-message').val().trim();

        if (!title) {
            Botble.showError(window.trans.api.please_enter_notification_title || 'Please enter a notification title.');
            $('#notification-title').focus();
            return;
        }

        if (!message) {
            Botble.showError(window.trans.api.please_enter_notification_message || 'Please enter a notification message.');
            $('#notification-message').focus();
            return;
        }

        // Disable submit button and show loading state
        submitBtn.prop('disabled', true);
        submitBtn.find('.ti-send').removeClass('ti-send').addClass('ti-loader');
        submitBtn.find('span').text(window.trans.api.notification_sending || 'Sending...');

        // Hide previous results
        resultDiv.hide();

        // Get form data
        const formData = {
            title: title,
            message: message,
            target: $('#notification-target').val(),
            action_url: $('#notification-action-url').val(),
            image_url: $('#notification-image-url').val(),
            _token: $('input[name="_token"]').val()
        };

        // Send AJAX request
        $.ajax({
            url: sendNotificationUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.error === false) {
                    showNotificationResult('success', response.message, response.data);
                    // Reset form fields
                    $('#notification-title').val('');
                    $('#notification-message').val('');
                    $('#notification-target').val('all');
                    $('#notification-action-url').val('');
                    $('#notification-image-url').val('');
                } else {
                    showNotificationResult('error', response.message, response.data);
                }
            },
            error: function(xhr) {
                let errorMessage = window.trans.api.notification_error_occurred || 'An error occurred while sending the notification.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showNotificationResult('error', errorMessage);
            },
            complete: function() {
                // Re-enable submit button and restore original state
                submitBtn.prop('disabled', false);
                submitBtn.find('.ti-loader').removeClass('ti-loader').addClass('ti-send');
                submitBtn.find('span').text(window.trans.api.send_notification || 'Send Notification');
            }
        });
    });

    // Handle Enter key in notification form fields
    $('#send-notification-form input, #send-notification-form textarea, #send-notification-form select').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) { // Enter key (but allow Shift+Enter in textarea)
            if ($(this).is('textarea')) {
                return; // Allow normal Enter behavior in textarea
            }
            e.preventDefault();
            $('#send-notification-btn').click();
        }
    });

    // Load device token stats on page load
    loadDeviceTokenStats();

    /**
     * Generate a random API key
     * @returns {string}
     */
    function generateRandomApiKey() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';

        // Generate a 32-character random string
        for (let i = 0; i < 32; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        return result;
    }

    /**
     * Update code examples with the current API key
     * @param {string} apiKey
     */
    function updateExamplesWithApiKey(apiKey) {
        const baseUrl = window.location.origin + '/api/v1';

        // Update cURL example
        const curlExample = `curl -X GET "${baseUrl}/pages" \\
     -H "Accept: application/json" \\
     -H "X-API-KEY: ${apiKey}"`;

        $('#curl-example').text(curlExample);

        // Update JavaScript example
        const jsExample = `fetch("${baseUrl}/pages", {
    method: "GET",
    headers: {
        "Accept": "application/json",
        "X-API-KEY": "${apiKey}"
    }
})
.then(response => response.json())
.then(data => console.log(data));`;

        $('#js-example').text(jsExample);
    }

    /**
     * Show notification result
     * @param {string} type - success or error
     * @param {string} message
     * @param {object} data - optional data object
     */
    function showNotificationResult(type, message, data = null) {
        const resultDiv = $('#notification-result');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconSvg = type === 'success'
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>';

        let content = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${iconSvg}
                <strong>${message}</strong>
        `;

        if (data && type === 'success') {
            const sentText = (window.trans.api.sent_to_devices || 'Sent to: :count devices').replace(':count', data.sent_count || 0);
            const failedText = (window.trans.api.failed_devices || 'Failed: :count devices').replace(':count', data.failed_count || 0);

            content += `
                <div class="mt-2">
                    <small>
                        ${sentText}<br>
                        ${failedText}
                    </small>
                </div>
            `;
        }

        content += `
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="${window.trans.api.close || 'Close'}"></button>
            </div>
        `;

        resultDiv.html(content).show();

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                resultDiv.find('.alert').alert('close');
            }, 5000);
        }
    }

    /**
     * Load device token statistics
     */
    function loadDeviceTokenStats() {
        $.ajax({
            url: deviceTokensStatsUrl,
            method: 'GET',
            success: function(response) {
                if (response.error === false && response.data) {
                    updateDeviceTokenStats(response.data);
                }
            },
            error: function() {
                // Silently fail - stats are not critical
            }
        });
    }

    /**
     * Update device token statistics in the UI
     * @param {object} stats
     */
    function updateDeviceTokenStats(stats) {
        // Update the notification send info text
        const infoText = $('#notification-send-info');
        if (infoText.length && stats.total > 0) {
            const deviceText = (window.trans.api.will_send_to_devices || 'Will send to :total active devices (:android Android, :ios iOS, :customers customers)')
                .replace(':total', stats.total)
                .replace(':android', stats.android)
                .replace(':ios', stats.ios)
                .replace(':customers', stats.customers);

            infoText.html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                ${deviceText}
            `);
        }
    }
});
