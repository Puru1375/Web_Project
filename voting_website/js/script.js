document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const otpForm = document.getElementById('otpForm');
    const createPollForm = document.getElementById('createPollForm');
    const addOptionBtn = document.getElementById('addOptionBtn');
    const pollsListDiv = document.getElementById('pollsList');
    const messageDiv = document.getElementById('message'); // General message div
    const voteMessageDiv = document.getElementById('voteMessage'); // Message div on dashboard specifically for votes
    const spinnerOverlay = document.querySelector('.spinner-overlay'); 



    const RECAPTCHA_V3_SITE_KEY_JS = '6LekrmkrAAAAAPMmyB-TJaDMhfwcNfS1Rm6uaMRk'; // Add your Site Key here too for JS access

    // js/script.js - inside registerForm listener
// js/script.js
if (registerForm) {
    registerForm.addEventListener('submit', function(e) { // Changed to non-async to manage flow explicitly
        e.preventDefault();
        const submitButton = registerForm.querySelector('button[type="submit"]');
        const recaptchaResponseInput = document.getElementById('recaptchaResponse');

        showLoading(submitButton); // Disable button, show spinner

        if (typeof grecaptcha === 'undefined' || typeof grecaptcha.ready === 'undefined') {
            console.error("grecaptcha object not available.");
            showMessage(messageDiv, "reCAPTCHA not loaded. Please refresh.", false);
            hideLoading(submitButton);
            return;
        }

        grecaptcha.ready(function() {
            grecaptcha.execute(RECAPTCHA_V3_SITE_KEY_JS, { action: 'register' }).then(function(token) {
                console.log("reCAPTCHA token from Google:", token);
                recaptchaResponseInput.value = token; // Set the token
                console.log("Value of hidden input 'recaptchaResponse':", recaptchaResponseInput.value);

                // NOW that the token is set in the hidden input, create FormData and make the call
                const formData = new FormData(registerForm);

                console.log("--- FormData being sent ---");
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                console.log("-------------------------");

                // Use a separate async function for the API call
                async function submitRegistration() {
                    try {
                        // Using direct fetch for clarity in this specific debug case
                        const fetchResponse = await fetch('api/register_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        const responseText = await fetchResponse.text();
                        console.log("Server Raw Response Text:", responseText);

                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (jsonError) {
                            console.error("Failed to parse server response as JSON:", jsonError, responseText);
                            showMessage(messageDiv, "Server returned an invalid response. Please check console.", false);
                            return; // Exit this async function
                        }

                        console.log("Parsed Data:", data);
                        showMessage(messageDiv, data.message, data.success);

                        if (data.success && data.otp_sent) {
                            window.location.href = `verify_otp.php?email=${encodeURIComponent(data.user_email)}`;
                        }
                    } catch (error) {
                        console.error("API Call/Network Error:", error);
                        showMessage(messageDiv, `Network or server error: ${error.message}`, false);
                    } finally {
                        hideLoading(submitButton); // Ensure button is re-enabled
                    }
                }

                submitRegistration(); // Call the async function

            }).catch(function(error) {
                console.error("reCAPTCHA execution error:", error);
                showMessage(messageDiv, "reCAPTCHA error. Please try again.", false);
                hideLoading(submitButton);
            });
        });
    });
}






    function showLoading(button = null, showGlobal = false) {
        if (button) {
            button.disabled = true;
            button.classList.add('loading');
            // Optionally add spinner element inside button if not using CSS only
            // const spinner = button.querySelector('.button-spinner') || document.createElement('span');
            // spinner.className = 'button-spinner';
            // button.appendChild(spinner); // Or make visible if already there
        }
        if (showGlobal && spinnerOverlay) {
            document.body.classList.add('loading'); // Shows the overlay via CSS
        }
    }

    function hideLoading(button = null, showGlobal = false) {
         if (button) {
            button.disabled = false;
            button.classList.remove('loading');
            // Remove spinner if added dynamically
            // const spinner = button.querySelector('.button-spinner');
            // if (spinner) spinner.remove(); // Or hide
        }
         if (showGlobal && spinnerOverlay) {
            document.body.classList.remove('loading'); // Hides the overlay via CSS
        }
    }



    // --- Helper Function for API Calls ---
    async function apiCall(url, formData,button = null) {
        showLoading(button);
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                // Try to get text for better error reporting
                let errorText = response.statusText;
                try {
                   errorText = await response.text();
                   console.error("Server Response Text (Error):", errorText);
                } catch(e){}
               throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
           }
            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            let errorMessage = `Network or server error: ${error.message}`;
         // Attempt to read response text if fetch failed after sending
         if (error.response && typeof error.response.text === 'function') {
            try {
                const text = await error.response.text();
                console.error("Server Response Text (Error):", text);
                // Avoid showing raw HTML errors directly to user if possible
             } catch (e) { /* ignore */ }
        }
            return { success: false, message: `Network or server error: ${errorMessage}` };
        }finally {
            hideLoading(button); // Hide button loading state regardless of success/failure
        }
    }

    // --- Display Messages ---
    function showMessage(element, text, isSuccess) {
        if (!element) return;
        element.textContent = text;
        element.className = 'message'; // Reset classes
        if (text) {
            element.classList.add(isSuccess ? 'success' : 'error');
        }
    }

    // --- Registration Form ---
    // if (registerForm) {
    //     registerForm.addEventListener('submit', async (e) => {
    //         e.preventDefault();
    //         const submitButton = registerForm.querySelector('button[type="submit"]');
    //         const formData = new FormData(registerForm);
    //         // Pass button to apiCall
    //         const response = await apiCall('api/register_handler.php', formData, submitButton);
    //         showMessage(messageDiv, response.message, response.success);
    //         if (response.success && response.otp_sent) {
    //              window.location.href = `verify_otp.php?email=${encodeURIComponent(response.user_email)}`;
    //         }
    //     });
    // }

    // --- OTP Verification Form ---
    if (otpForm) {
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
             const submitButton = otpForm.querySelector('button[type="submit"]');
            const formData = new FormData(otpForm);
            const response = await apiCall('api/otp_handler.php', formData, submitButton);
            showMessage(messageDiv, response.message, response.success);
            if (response.success) {
                 showMessage(messageDiv, response.message + " Redirecting...", true); // Add redirect message
                setTimeout(() => { window.location.href = 'index.php'; }, 2000);
            }
        });
    }

    // --- Login Form ---
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const formData = new FormData(loginForm);
            const response = await apiCall('api/login_handler.php', formData, submitButton);
            showMessage(messageDiv, response.message, response.success);
            if (response.success) {
                window.location.href = 'dashboard.php';
            }
        });
    }

    // --- Create Poll Form ---
    if (createPollForm) {
        // Add Option Button Handler
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', () => {
                const optionsContainer = document.getElementById('optionsContainer');
                const optionCount = optionsContainer.querySelectorAll('.option-group').length + 1;

                const newOptionDiv = document.createElement('div');
                newOptionDiv.className = 'form-group option-group';
                newOptionDiv.innerHTML = `
                    <label>Option ${optionCount}:</label>
                    <input type="text" name="options[]" required>
                     <button type="button" class="remove-option-btn" onclick="this.parentElement.remove()">Remove</button>
                `;
                optionsContainer.appendChild(newOptionDiv);
            });
        }

        // Form Submission
        createPollForm.addEventListener('submit', async (e) => {
            e.preventDefault();
             // Basic client-side validation (ensure at least two options)
             const optionsInputs = createPollForm.querySelectorAll('input[name="options[]"]');
             let validOptionsCount = 0;
             optionsInputs.forEach(input => {
                 if (input.value.trim() !== '') {
                     validOptionsCount++;
                 }
             });

             if (validOptionsCount < 2) {
                 showMessage(messageDiv, "Please provide at least two non-empty options.", false);
                 return;
             }


             const submitButton = createPollForm.querySelector('button[type="submit"]');
             const formData = new FormData(createPollForm);
             const response = await apiCall('api/create_poll_handler.php', formData, submitButton);
             showMessage(messageDiv, response.message, response.success);
             if (response.success) {
                  showMessage(messageDiv, response.message + " Redirecting...", true);
                  setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
             }
        });
    }

    // --- Load Polls on Dashboard ---
    async function loadPolls() {
        if (!pollsListDiv) return; // Only run on dashboard page
        showLoading(null, true); // Show global overlay for initial load
        pollsListDiv.innerHTML = '<p><i>Loading polls...</i></p>'; // Improved loading text
    
        try {
            // Fetch the polls list (includes 'has_voted' status)
            const response = await fetch('api/get_polls.php');
            console.log("Raw fetch response object:", response); 
             if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
    
            pollsListDiv.innerHTML = ''; // Clear loading message
    
            if (data.success && data.polls.length > 0) {
                data.polls.forEach(poll => {
                    console.log(`Processing Poll ID: ${poll.id}, is_open from API: ${poll.is_open}, has_voted: ${poll.has_voted}`);
                    const pollElement = document.createElement('div');
                    pollElement.className = 'poll-item';
                    pollElement.dataset.pollId = poll.id; // Store poll ID

                    // Display closing time if set
                    let closesAtText = '';
                    if (poll.closes_at_formatted) {
                         closesAtText = `<p class="poll-meta closes-info">Closes: ${poll.closes_at_formatted}</p>`;
                    } else {
                         closesAtText = `<p class="poll-meta closes-info">Never closes</p>`;
                    }
                    // Add a specific status message for closed polls
                    let pollStatusText = !poll.is_open ? '<p class="poll-status-closed"><strong>Status: Closed</strong></p>' : '';        
    
                    pollElement.innerHTML = `
                        <h3>${escapeHtml(poll.question)}</h3>
                        <p class="poll-meta">Created by ${escapeHtml(poll.created_by)} on ${poll.created_at}</p>
                        <div class="poll-options-container">
                            <!-- Options or Results will load here -->
                            <p><i>Loading options...</i></p>
                        </div>
                         <div class="poll-actions">
                              ${!poll.has_voted ? '<button class="vote-button" style="display:none;">Submit Vote</button>' : ''}
                              <button class="view-results-button">View Results</button>
                         </div>
                    `;
                    pollsListDiv.appendChild(pollElement);
    
                    // Load options/results for this specific poll
                    if (poll.has_voted) {
                        // If already voted, immediately show results
                        displayPollResults(pollElement, poll.id);
                    } else {
                        // If not voted, display the voting options
                        displayVotingOptions(pollElement, poll);
                    }
                });
    
                // Add event listeners AFTER elements are created
                setupActionListeners(); // New function to handle vote/results buttons
    
            } else if (data.success && data.polls.length === 0) {
                pollsListDiv.innerHTML = '<p>No polls available yet. Why not create one?</p>';
            } else {
                 pollsListDiv.innerHTML = `<p class="error">Error loading polls: ${data.message || 'Unknown error'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching polls:', error);
            pollsListDiv.innerHTML = `<p class="error">Failed to load polls. ${error.message}</p>`;
        }finally {
            hideLoading(null, true); // Hide global overlay
       }
    }

    function displayVotingOptions(pollElement, poll) {
        const container = pollElement.querySelector('.poll-options-container');
        const voteButton = pollElement.querySelector('.vote-button');
        container.innerHTML = ''; // Clear loading message
    
         if (poll.options.length > 0) {
            let optionsHtml = poll.options.map(option => `
                <div class="poll-option">
                    <input type="radio" name="poll_${poll.id}" id="option_${option.id}" value="${option.id}" required>
                    <label for="option_${option.id}">${escapeHtml(option.text)}</label>
                </div>
            `).join('');
    
            const form = document.createElement('form');
            form.className = 'voteForm';
            form.innerHTML = `
                <input type="hidden" name="poll_id" value="${poll.id}">
                ${optionsHtml}
            `;
            container.appendChild(form);
            if(voteButton) voteButton.style.display = 'inline-block'; // Show the vote button
    
        } else {
            container.innerHTML = '<p><i>No options available for this poll.</i></p>';
             if(voteButton) voteButton.style.display = 'none';
        }
    }

    async function displayPollResults(pollElement, pollId) {
        const container = pollElement.querySelector('.poll-options-container');
        const voteButton = pollElement.querySelector('.vote-button');
        const resultsButton = pollElement.querySelector('.view-results-button');
    
        // Hide vote button, change results button text while loading
        if(voteButton) voteButton.style.display = 'none';
        if(resultsButton) {
          resultsButton.textContent = 'Loading Results...';
          resultsButton.disabled = true;
        }
        
        showLoading(resultsButton);
        container.innerHTML = '<p><i>Loading results...</i></p>';
    
        try {
            const response = await fetch(`api/get_poll_results.php?poll_id=${pollId}`);
            if (!response.ok) {
                 throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
    
            container.innerHTML = ''; // Clear loading message
    
            if (data.success && data.results) {
                let resultsHtml = `<p><strong>Total Votes: ${data.total_votes}</strong></p>`;
                resultsHtml += '<ul class="poll-results-list">';
    
                data.results.forEach(result => {
                    resultsHtml += `
                        <li>
                            <span class="result-text">${escapeHtml(result.text)}:</span>
                            <span class="result-count">${result.count} vote(s)</span>
                            <span class="result-percentage">(${result.percentage}%)</span>
                            <div class="result-bar-container">
                                <div class="result-bar" style="width: ${result.percentage}%;"></div>
                            </div>
                        </li>
                    `;
                });
                resultsHtml += '</ul>';
                container.innerHTML = resultsHtml;
    
                // Restore results button (optional: change text?)
                 if(resultsButton) {
                    resultsButton.textContent = 'View Results'; // Or 'Hide Results' if you add toggle logic
                    resultsButton.disabled = false; // Re-enable
                 }
    
            } else {
                container.innerHTML = `<p class="error">Error loading results: ${data.message || 'Unknown error'}</p>`;
                 if(resultsButton) { // Restore button on error too
                    resultsButton.textContent = 'View Results';
                    resultsButton.disabled = false;
                 }
            }
        } catch (error) {
            console.error('Error fetching poll results:', error);
            container.innerHTML = `<p class="error">Failed to load results. ${error.message}</p>`;
             if(resultsButton) { // Restore button on error too
                resultsButton.textContent = 'View Results';
                resultsButton.disabled = false;
             }
        } finally {
            hideLoading(resultsButton); // Hide button loading state
        }
    }

    function setupActionListeners() {
        const pollItems = document.querySelectorAll('.poll-item');
        pollItems.forEach(item => {
            const pollId = item.dataset.pollId;
            const voteButton = item.querySelector('.vote-button');
            const resultsButton = item.querySelector('.view-results-button');
            const form = item.querySelector('.voteForm'); // Might be null if results shown initially
   
             // Vote Button Listener (attached to form if exists)
             if (form && voteButton) {
                 form.addEventListener('submit', async (e) => {
                      e.preventDefault(); // Prevent default form submission
   
                      const selectedOption = form.querySelector(`input[name="poll_${pollId}"]:checked`);
   
                      if (!selectedOption) {
                          showMessage(voteMessageDiv, 'Please select an option to vote.', false);
                          setTimeout(() => showMessage(voteMessageDiv, '', false), 3000);
                          return;
                      }
   
                      const formData = new FormData();
                      formData.append('poll_id', pollId);
                      formData.append('option_id', selectedOption.value);

                      // ---> Get CSRF token from the pollsList data attribute <---
                      const pollsContainer = document.getElementById('pollsList');
                      const csrfToken = pollsContainer ? pollsContainer.dataset.csrf : null;
                         if (!csrfToken) {
                           showMessage(voteMessageDiv, 'Error: Missing security token. Please refresh.', false);
                           console.error('CSRF token missing from #pollsList data attribute');
                           return; // Stop submission
                      }
                      formData.append('csrf_token', csrfToken); // Add token to vote data
                      // ---> End CSRF token addition <---

   
                      // Disable button while submitting
                      voteButton.disabled = true;
                      voteButton.textContent = 'Submitting...';
                      if (resultsButton) resultsButton.disabled = true; // Disable results button too
   
                      const response = await apiCall('api/submit_vote.php', formData, voteButton);
   
                      showMessage(voteMessageDiv, response.message, response.success);
                      setTimeout(() => showMessage(voteMessageDiv, '', false), 4000);
   
                      if (response.success) {
                          // Vote successful, now display results for this poll
                          displayPollResults(item, pollId);
                      } else {
                          // Re-enable buttons on failure
                          voteButton.disabled = false;
                          voteButton.textContent = 'Submit Vote';
                           if (resultsButton) resultsButton.disabled = false;
                      }
                  });
   
                   // Move the button association here - trigger form submit when button is clicked
                   voteButton.addEventListener('click', () => {
                      // Find the form within the same poll item and submit it
                      const associatedForm = item.querySelector('.voteForm');
                      if(associatedForm){
                          // Trigger the form's submit event programmatically
                          // Create a submit event
                           const submitEvent = new Event('submit', {
                              'bubbles': true,
                              'cancelable': true
                           });
                           associatedForm.dispatchEvent(submitEvent);
                      } else {
                          console.error("Could not find form associated with vote button for poll:", pollId);
                      }
                   });
   
             }
   
             // Results Button Listener            hfpwudzybvhblncw
             if (resultsButton) {
                 resultsButton.addEventListener('click', () => {
                     // Fetch and display results when clicked
                     displayPollResults(item, pollId);
                 });
             }
   
        });
   }

    // --- Setup Vote Button Listeners ---
    

    // --- Helper to prevent XSS ---
     function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
             .replace(/&/g, "&")
             .replace(/</g, "<")
             .replace(/>/g, ">")
             .replace(/'/g, "'")
             .replace(/'/g, "'");
     }


    // --- Initial Page Load Actions ---
    if (pollsListDiv) {
        loadPolls(); // Load polls if on dashboard
    }

}); // End DOMContentLoaded

// js/script.js

document.addEventListener('DOMContentLoaded', () => {
    // ... (all your existing code) ...

    // --- Mobile Navigation Toggle ---
    const navToggleButtons = document.querySelectorAll('.nav-toggle'); // Get all toggles

    navToggleButtons.forEach(navToggle => {
        // Find the associated menu, assuming it's the next .main-nav sibling or has a specific ID
        // For this example, we gave the nav menu an ID: "mainNavMenu"
        // This assumes only one mainNavMenu per page, or you'd need a more specific selector
        const mainNavMenu = document.getElementById('mainNavMenu');

        if (navToggle && mainNavMenu) {
            navToggle.addEventListener('click', () => {
                const isOpen = mainNavMenu.classList.contains('open');
                navToggle.setAttribute('aria-expanded', !isOpen);
                mainNavMenu.classList.toggle('open');
                navToggle.classList.toggle('open'); // For hamburger animation
            });
        }
    });

}); // End DOMContentLoaded          hckf mwrn igmz objp