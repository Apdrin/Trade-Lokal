// Function to handle "View Details" button click
function viewDetails(productName) {
    alert("You clicked on: " + productName + "\n\nIn a real website, this would open a detailed product page!");
}

// Function to handle product search filtering
function filterProducts() {
    // Get the search input value
    var input = document.getElementById('searchInput');
    var filter = input.value.toLowerCase();
    
    // Get all product items
    var productContainer = document.getElementById("productGrid");
    var products = productContainer.getElementsByClassName('product-item');

    // Loop through all products and hide those that don't match the search
    for (var i = 0; i < products.length; i++) {
        var productName = products[i].getAttribute("data-name");
        
        if (productName) {
            // Check if the search text matches the product data-name
            if (productName.toLowerCase().indexOf(filter) > -1) {
                products[i].style.display = ""; // Show
            } else {
                products[i].style.display = "none"; // Hide
            }
        }
    }
}

// Optional: Trigger search when pressing "Enter" key
document.getElementById("searchInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") {
        filterProducts();
    }
});