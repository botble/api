$(() => {
    'use strict';

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
        const apiKey = $(this).val() || 'your-api-key-here';
        updateExamplesWithApiKey(apiKey);

        // Show/hide copy button based on whether there's a value
        const copyButton = $('#copy-api-key');
        if (apiKey && apiKey !== 'your-api-key-here') {
            copyButton.show();
        } else {
            copyButton.hide();
        }
    });

    // Initialize examples on page load
    const currentApiKey = $('#api-key-input').val() || 'your-api-key-here';
    updateExamplesWithApiKey(currentApiKey);

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
        const curlExample = `curl -X GET "${baseUrl}/products" \\
     -H "Accept: application/json" \\
     -H "X-API-KEY: ${apiKey}"`;
        
        $('#curl-example').text(curlExample);
        
        // Update JavaScript example
        const jsExample = `fetch("${baseUrl}/products", {
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
});
