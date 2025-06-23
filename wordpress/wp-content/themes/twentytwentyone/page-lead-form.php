<?php

/**
 * Template Name: Lead Submission Form
 * Description: Custom page template for submitting leads to the Laravel API.
 */

$api_url = 'http://localhost/api/leads';
$api_bearer_token = 'hUXTOA6r6xRTAVf5OEhFsoYZ2gy7EkzO5fOH57ZbKaJmvRcAM4gsZrGjItCg1Skl';

get_header();
?>

<style>
    @import url('https://fonts.googleapis.com/css?family=Fjalla+One&display=swap');

    :root {
        --form-bg: #f8f4e5;
        --form-shadow: #ffa580;
        --form-primary-highlight: #95a4ff;
        --form-secondary-highlight: #ffc8ff;
        --font-size: 14pt;
        --font-face: 'Fjalla One', sans-serif;
        --font-color: #2A293E;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: url("https://s3-us-west-2.amazonaws.com/s.cdpn.io/38816/image-from-rawpixel-id-2210775-jpeg.jpg") center center no-repeat;
        background-size: cover;
        width: 100vw;
        height: 100vh;
        display: grid;
        align-items: center;
        justify-items: center;
        font-family: var(--font-face);
        color: var(--font-color);
    }

    #primary,
    #main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: auto;
    }

    .contact-us {
        background: var(--form-bg);
        padding: 50px 100px;
        border: 2px solid rgba(0, 0, 0, 1);
        box-shadow: 15px 15px 1px var(--form-shadow), 15px 15px 1px 2px rgba(0, 0, 0, 1);
        font-family: var(--font-face);
        color: var(--font-color);
    }

    .contact-us h2 {
        margin: 0 0 calc(var(--font-size) * 2);
        padding: 0;
        color: var(--font-color);
        text-align: center;
        font-size: calc(var(--font-size) * 1.5);
    }

    .form-group {
        position: relative;
        margin-bottom: calc(var(--font-size) * 2);
    }

    .form-group input {
        display: block;
        width: 100%;
        font-size: var(--font-size);
        line-height: calc(var(--font-size) * 2);
        font-family: var(--font-face);
        border: none;
        border-bottom: 5px solid rgba(0, 0, 0, 1);
        background: var(--form-bg);
        min-width: 250px;
        padding-left: 5px;
        outline: none;
        color: rgba(0, 0, 0, 1);
        padding-bottom: 5px;
    }

    .form-group input:focus {
        border-bottom: 5px solid var(--form-shadow);
    }

    .form-group label {
        position: absolute;
        left: 0;
        top: 0;
        font-size: var(--font-size);
        color: #777;
        pointer-events: none;
        transition: 0.3s ease all;
    }

    .form-group input:focus~label,
    .form-group input:valid~label {
        top: -20px;
        font-size: calc(var(--font-size) * 0.7);
        color: var(--form-primary-highlight);
    }

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
        border-bottom: 5px solid var(--form-primary-highlight);
        -webkit-text-fill-color: var(--font-color);
        -webkit-box-shadow: 0 0 0px 1000px var(--form-bg) inset;
        transition: background-color 5000s ease-in-out 0s;
    }

    .form-group input.error {
        border-bottom: 5px solid #e74c3c;
    }

    .error-message {
        color: #e74c3c;
        font-size: 0.8em;
        margin-top: 5px;
        display: none;
        text-align: left;
        font-family: 'Roboto', sans-serif;
    }

    button#submitButton {
        display: block;
        margin: 0 auto;
        line-height: calc(var(--font-size) * 2);
        padding: 0 20px;
        background: var(--form-shadow);
        letter-spacing: 2px;
        transition: .2s all ease-in-out;
        outline: none;
        border: 1px solid rgba(0, 0, 0, 1);
        box-shadow: 3px 3px 1px 1px var(--form-primary-highlight), 3px 3px 1px 2px rgba(0, 0, 0, 1);
        font-family: var(--font-face);
        font-size: var(--font-size);
        cursor: pointer;
        color: var(--font-color);
    }

    button#submitButton:hover {
        background: rgba(0, 0, 0, 1);
        color: white;
        border: 1px solid rgba(0, 0, 0, 1);
    }

    button:disabled {
        background: #ccc;
        color: #888;
        border: 1px solid #888;
        cursor: not-allowed;
        box-shadow: none;
    }

    .loading-spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: var(--font-color);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: inline-block;
        vertical-align: middle;
        margin-right: 10px;
        display: none;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .message-box {
        margin-top: 20px;
        padding: 10px;
        border-radius: 5px;
        font-size: 1em;
        font-weight: bold;
        text-align: center;
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease;
        border: 1px solid;
    }

    .message-box.success {
        background-color: rgba(149, 164, 255, 0.2);
        color: var(--form-primary-highlight);
        border-color: var(--form-primary-highlight);
    }

    .message-box.error {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border-color: #e74c3c;
    }

    ::selection {
        background: var(--form-secondary-highlight);
    }
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="contact-us">
            <h2>Lead Submission</h2>
            <form id="leadForm">
                <div class="form-group">
                    <input type="text" id="name" name="name" required class="form-control">
                    <label class="form-label" for="name">Name</label>
                    <div class="error-message" id="name-error">Name is required.</div>
                </div>

                <div class="form-group">
                    <input type="email" id="email" name="email" required class="form-control">
                    <label class="form-label" for="email">Email</label>
                    <div class="error-message" id="email-error">Please enter a valid email address.</div>
                </div>

                <div class="form-group">
                    <input type="tel" id="phone" name="phone" required class="form-control">
                    <label class="form-label" for="phone">Phone Number</label>
                    <div class="error-message" id="phone-error">Please enter a valid phone number (e.g., 628123456789 or 08123456789).</div>
                </div>

                <input type="hidden" id="source" name="source" value="unknown">

                <button type="submit" id="submitButton" class="btn">
                    <span class="loading-spinner" id="spinner"></span>
                    Submit Lead
                </button>

                <div class="message-box" id="responseMessage"></div>
            </form>
        </div>
    </main>
</div>

<script>
    jQuery(document).ready(function($) {
        const form = document.getElementById('leadForm');

        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const sourceInput = document.getElementById('source');
        const submitButton = document.getElementById('submitButton');
        const spinner = document.getElementById('spinner');
        const responseMessage = document.getElementById('responseMessage');

        const apiEndpoint = '<?php echo $api_url; ?>';
        const bearerToken = '<?php echo $api_bearer_token; ?>';

        function getUtmParams() {
            const params = new URLSearchParams(window.location.search);
            const utmSource = params.get('utm_source') || params.get('source') || 'direct_visit';
            console.log('UTM Source captured:', utmSource);
            return utmSource;
        }

        sourceInput.value = getUtmParams();

        function validateInput(input, errorMessageElement, validationFn) {
            if (!validationFn(input.value)) {
                input.classList.add('error');
                errorMessageElement.style.display = 'block';
                return false;
            } else {
                input.classList.remove('error');
                errorMessageElement.style.display = 'none';
                return true;
            }
        }

        const validateName = (name) => name.trim() !== '';
        const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        const validatePhone = (phone) => /^(\+62|62|0)8[1-9][0-9]{6,9}$/.test(phone.replace(/[\s()-]/g, ''));

        nameInput.addEventListener('input', () => validateInput(nameInput, document.getElementById('name-error'), validateName));
        emailInput.addEventListener('input', () => validateInput(emailInput, document.getElementById('email-error'), validateEmail));
        phoneInput.addEventListener('input', () => validateInput(phoneInput, document.getElementById('phone-error'), validatePhone));


        form.addEventListener('submit', async function(event) {
            event.preventDefault();


            responseMessage.style.opacity = '0';
            responseMessage.style.display = 'none';
            responseMessage.classList.remove('success', 'error');

            const isNameValid = validateInput(nameInput, document.getElementById('name-error'), validateName);
            const isEmailValid = validateInput(emailInput, document.getElementById('email-error'), validateEmail);
            const isPhoneValid = validateInput(phoneInput, document.getElementById('phone-error'), validatePhone);

            if (!isNameValid || !isEmailValid || !isPhoneValid) {
                displayMessage('Please correct the errors in the form.', 'error');
                return;
            }

            submitButton.disabled = true;
            spinner.style.display = 'inline-block';

            const formData = {
                name: nameInput.value,
                email: emailInput.value,
                phone: phoneInput.value,
                source: sourceInput.value,
                message: 'Lead submitted via WordPress form'
            };

            try {
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${bearerToken}`
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    displayMessage(data.message || 'Lead submitted successfully!', 'success');
                    form.reset();
                    sourceInput.value = getUtmParams();
                } else {
                    const errorMessage = data.message || 'Failed to submit lead.';
                    const errorDetails = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                    displayMessage(`${errorMessage} ${errorDetails}`, 'error');
                }
            } catch (error) {
                console.error('Error submitting lead:', error);
                displayMessage('Network error or API is unreachable. Please try again later.', 'error');
            } finally {
                submitButton.disabled = false;
                spinner.style.display = 'none';
            }
        });

        function displayMessage(message, type) {
            responseMessage.textContent = message;
            responseMessage.classList.add(type);
            responseMessage.style.display = 'block';
            responseMessage.style.opacity = '1';
        }

    });
</script>

<?php
get_footer();
