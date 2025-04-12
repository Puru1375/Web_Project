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

// --- Run when DOM is ready ---
document.addEventListener('DOMContentLoaded', () => {
    displayProducts();
    handlePriceComparison();
    // Check login status could go here if needed later
});