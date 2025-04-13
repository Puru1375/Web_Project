// script.js - Logic for index.html

// Sample Product Data (Use a placeholder image in your images/ folder)
const products = [
    { id: 1, name: "Wireless Headphones", img: "images/product-placeholder.jpg", originalPrice: 3000, currentPrice: 2250, discount: "25% off", link: "#" },
    { id: 2, name: "Stylish Backpack", img: "images/product-placeholder.jpg", originalPrice: 1500, currentPrice: 1200, discount: "₹300 off", link: "#" },
    { id: 3, name: "Coffee Maker", img: "images/product-placeholder.jpg", originalPrice: null, currentPrice: 4000, discount: "New Arrival", link: "#" },
    { id: 4, name: "Running Shoes", img: "images/product-placeholder.jpg", originalPrice: 5000, currentPrice: 3500, discount: "30% off", link: "#" }
];

// Function to create HTML for a single product card
function createProductCardHTML(product) {
    let priceHTML = `<p class="price">₹${product.currentPrice}</p>`;
    if (product.originalPrice) {
        priceHTML = `<p class="price"><span class="original-price">₹${product.originalPrice}</span> ₹${product.currentPrice}</p>`;
    }
    return `
        <article class="product-card" data-id="${product.id}">
            <img src="${product.img}" alt="${product.name}">
            <h3>${product.name}</h3>
            ${priceHTML}
            <p class="discount">${product.discount}</p>
            <a href="${product.link}" target="_blank" class="buy-button">View Deal</a>
        </article>
    `;
}

// Function to display all products in the grid
function displayProducts() {
    const productGrid = document.querySelector('.product-grid');
    if (!productGrid) return;
    productGrid.innerHTML = ''; // Clear loading message
    if (products.length === 0) {
        productGrid.innerHTML = '<p>No discounted products found.</p>';
        return;
    }
    products.forEach(product => {
        productGrid.insertAdjacentHTML('beforeend', createProductCardHTML(product));
    });
}

// --- Price Comparison Simulation ---
function handlePriceComparison() {
    const compareForm = document.getElementById('compare-form');
    const resultsDiv = document.getElementById('comparison-results');
    const productLinkInput = document.getElementById('product-link');
    if (!compareForm) return;

    compareForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const productUrl = productLinkInput.value;
        if (!productUrl) {
            resultsDiv.innerHTML = '<p style="color: red;">Please enter a product link.</p>';
            return;
        }
        resultsDiv.innerHTML = '<p>Searching for prices... (Simulation)</p>';
        setTimeout(() => { // Simulate delay
            let resultHTML = '<p>Comparison results:</p><ul>';
            if (productUrl.includes('amazon')) {
                resultHTML += `<li>Amazon.in: Price found (Your Link) <a href="${productUrl}" target="_blank">View</a></li><li>Flipkart.com: <span style="color: green;">₹XXX (Simulated Lowest)</span> <a href="#" target="_blank">View Mock</a></li>`;
            } else if (productUrl.includes('flipkart')) {
                resultHTML += `<li>Flipkart.com: Price found (Your Link) <a href="${productUrl}" target="_blank">View</a></li><li>Amazon.in: <span style="color: green;">₹YYY (Simulated Lowest)</span> <a href="#" target="_blank">View Mock</a></li>`;
            } else {
                resultHTML += `<li>Could not find reliable comparisons for this link in simulation.</li>`;
            }
            resultHTML += '</ul><p><em>Note: This is a frontend simulation. Real comparison requires backend systems.</em></p>';
            resultsDiv.innerHTML = resultHTML;
        }, 1500);
    });
}


// script.js - Logic for index.html

// --- (Keep existing product data and display functions) ---

// --- NEW: Function to check login status and update Nav ---
async function checkLoginStatusAndUpdateNav() {
    const navLogin = document.getElementById('nav-login');
    const navRegister = document.getElementById('nav-register');
    const navUserInfo = document.getElementById('nav-user-info');
    const navUsername = document.getElementById('nav-username');
    const navLogout = document.getElementById('nav-logout');
    const logoutButton = document.getElementById('logout-button');

    // Ensure all elements exist before proceeding
    if (!navLogin || !navRegister || !navUserInfo || !navUsername || !navLogout || !logoutButton) {
        console.error("Navigation elements not found!");
        return;
    }

    try {
        const response = await fetch('api/check_session.php'); // Call the PHP session check script
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();

        if (data.loggedIn) {
            // User IS logged in
            navLogin.style.display = 'none';
            navRegister.style.display = 'none';

            navUserInfo.style.display = 'inline'; // Or 'block'/'flex' depending on layout
            navUsername.textContent = data.username || 'User'; // Display username
            navLogout.style.display = 'inline'; // Or 'block'/'flex'

            // Add event listener for logout ONLY if it's not already added
            if (!logoutButton.dataset.listenerAttached) {
                 logoutButton.addEventListener('click', handleLogout);
                 logoutButton.dataset.listenerAttached = 'true'; // Mark as attached
            }

        } else {
            // User IS NOT logged in
            navLogin.style.display = 'inline'; // Or 'block'/'flex'
            navRegister.style.display = 'inline'; // Or 'block'/'flex'

            navUserInfo.style.display = 'none';
            navLogout.style.display = 'none';
        }

    } catch (error) {
        console.error('Error checking login status:', error);
        // Keep default nav state (Login/Register visible) if there's an error
         navLogin.style.display = 'inline';
         navRegister.style.display = 'inline';
         navUserInfo.style.display = 'none';
         navLogout.style.display = 'none';
    }
}

// --- NEW: Function to handle logout ---
async function handleLogout() {
    try {
        const response = await fetch('api/logout_handler.php', { method: 'POST' }); // Send request to logout script
         if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const result = await response.json();

        if (result.success) {
            // Logout successful, reload the page to reflect changes
            window.location.reload();
        } else {
            console.error('Logout failed:', result.message);
            alert('Logout failed. Please try again.'); // Inform user
        }
    } catch (error) {
        console.error('Error during logout:', error);
        alert('An error occurred during logout.');
    }
}


// --- UPDATED: Run when DOM is ready ---
document.addEventListener('DOMContentLoaded', () => {
    displayProducts();          // Display products (existing function)
    handlePriceComparison();    // Set up comparison form (existing function)
    checkLoginStatusAndUpdateNav(); // **NEW: Check login status and update nav**
});