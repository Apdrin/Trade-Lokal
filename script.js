// Function to handle "View Details" button click
function viewDetails(productName) {
  alert(
    "You clicked on: " +
      productName +
      "\n\nIn a real website, this would open a detailed product page!",
  );
}

// Function to handle product search filtering
function filterProducts() {
  // Get the search input value - try different possible input IDs
  var input =
    document.getElementById("searchInput") ||
    document.getElementById("categorySearchInput") ||
    document.querySelector(".search-bar input");
  if (!input) return;

  var filter = input.value.toLowerCase().trim();

  // Get product container - try different possible container IDs
  var productContainer =
    document.getElementById("productGrid") ||
    document.getElementById("product-list");
  if (!productContainer) return;

  var products = productContainer.getElementsByClassName("product-item");

  // Check if we have a "no products" message element, create one if not
  var noProductsMsg = document.getElementById("no-products-message");
  if (!noProductsMsg) {
    noProductsMsg = document.createElement("div");
    noProductsMsg.id = "no-products-message";
    noProductsMsg.className = "text-center mt-4";
    noProductsMsg.innerHTML =
      '<p class="text-muted">No products found matching your search.</p>';
    productContainer.parentNode.insertBefore(
      noProductsMsg,
      productContainer.nextSibling,
    );
  }

  var visibleCount = 0;

  // Loop through all products and hide those that don't match the search
  for (var i = 0; i < products.length; i++) {
    var productName = products[i].getAttribute("data-name");

    if (productName) {
      // Check if the search text matches the product data-name
      if (productName.toLowerCase().indexOf(filter) > -1) {
        products[i].style.display = ""; // Show
        visibleCount++;
      } else {
        products[i].style.display = "none"; // Hide
      }
    }
  }

  // Show/hide the "no products found" message
  if (visibleCount === 0 && filter !== "") {
    noProductsMsg.style.display = "block";
  } else {
    noProductsMsg.style.display = "none";
  }
}

// Optional: Trigger search when pressing "Enter" key
document.addEventListener("DOMContentLoaded", function () {
  // Handle search input on main page
  var mainSearchInput = document.getElementById("searchInput");
  if (mainSearchInput) {
    mainSearchInput.addEventListener("keyup", function (event) {
      if (event.key === "Enter") {
        filterProducts();
      }
    });
  }

  // Handle search inputs on category pages
  var categorySearchInputs = document.querySelectorAll(".search-bar input");
  categorySearchInputs.forEach(function (input) {
    input.addEventListener("keyup", function (event) {
      if (event.key === "Enter") {
        filterProducts();
      }
    });
  });

  // Handle search buttons
  var searchButtons = document.querySelectorAll(".search-btn");
  searchButtons.forEach(function (button) {
    button.addEventListener("click", filterProducts);
  });
});
