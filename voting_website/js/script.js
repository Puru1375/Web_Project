document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const otpForm = document.getElementById('otpForm');
    const createPollForm = document.getElementById('createPollForm');
    const addOptionBtn = document.getElementById('addOptionBtn');
    const pollsListDiv = document.getElementById('pollsList');
    const messageDiv = document.getElementById('message'); // General message div
    const voteMessageDiv = document.getElementById('voteMessage'); // Message div on dashboard specifically for votes

    // --- Helper Function for API Calls ---
    async function apiCall(url, formData) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
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
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const response = await apiCall('api/register_handler.php', formData);

            showMessage(messageDiv, response.message, response.success);

            if (response.success && response.otp_sent) {
                // Redirect to OTP verification page, passing email
                // Also pass the demo OTP in the URL for testing (REMOVE IN PRODUCTION)
                let redirectUrl = `verify_otp.php?email=${encodeURIComponent(response.user_email)}`;
                if (response.demo_otp) {
                     redirectUrl += `&demo_otp=${response.demo_otp}`; // FOR DEMO ONLY
                     alert(`DEMO ONLY: OTP is ${response.demo_otp}`);
                }
                window.location.href = redirectUrl;
            }
        });
    }

    // --- OTP Verification Form ---
    if (otpForm) {
        // Pre-fill OTP if passed in URL (DEMO ONLY)
        // const urlParams = new URLSearchParams(window.location.search);
        // const demoOtp = urlParams.get('demo_otp');
        // const otpInput = document.getElementById('otp');
        //  if (demoOtp && otpInput) {
        //      otpInput.value = demoOtp;
        //  }
         // Auto-fill email from URL param into hidden field if needed (already done with PHP echo)
         // const emailParam = urlParams.get('email');
         // const emailInput = document.getElementById('email');
         // if(emailParam && emailInput) emailInput.value = emailParam;

        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(otpForm);
            const response = await apiCall('api/otp_handler.php', formData);

            showMessage(messageDiv, response.message, response.success);

            if (response.success) {
                // Redirect to login page after a short delay
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000); // Wait 2 seconds
            }
        });
    }

    // --- Login Form ---
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const response = await apiCall('api/login_handler.php', formData);

            showMessage(messageDiv, response.message, response.success);

            if (response.success) {
                // Redirect to dashboard
                window.location.href = 'dashboard.php';
            }
            // Optional: Handle OTP redirect if needed
            // if (response.redirect_otp && response.user_email) {
            //     window.location.href = `verify_otp.php?email=${encodeURIComponent(response.user_email)}`;
            // }
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


            const formData = new FormData(createPollForm);
            const response = await apiCall('api/create_poll_handler.php', formData);

            showMessage(messageDiv, response.message, response.success);

            if (response.success) {
                // Optionally clear the form or redirect
                // createPollForm.reset();
                // optionsContainer.innerHTML = ''; // Clear dynamically added options too if needed
                 setTimeout(() => {
                    window.location.href = 'dashboard.php'; // Redirect back to dashboard
                }, 1500);
            }
        });
    }

    // --- Load Polls on Dashboard ---
    async function loadPolls() {
        if (!pollsListDiv) return; // Only run on dashboard page
    
        try {
            // Fetch the polls list (includes 'has_voted' status)
            const response = await fetch('api/get_polls.php');
             if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
    
            pollsListDiv.innerHTML = ''; // Clear loading message
    
            if (data.success && data.polls.length > 0) {
                data.polls.forEach(poll => {
                    const pollElement = document.createElement('div');
                    pollElement.className = 'poll-item';
                    pollElement.dataset.pollId = poll.id; // Store poll ID
    
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
   
                      const response = await apiCall('api/submit_vote.php', formData);
   
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
   
             // Results Button Listener
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