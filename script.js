const summaryEl = document.getElementById("orderSummary");
const totalEl = document.getElementById("orderTotal");
const totalInput = document.getElementById("orderTotalInput");
const summaryInput = document.getElementById("orderItemsSummaryInput");
const cartItemsInput = document.getElementById("cartItemsInput");
const form = document.getElementById("orderForm");
const pickupDateInput = document.getElementById("pickupDate");

const cart = {};

function buildOrderSummaryText() {
  return Object.entries(cart)
    .map(([name, item]) => `${name} x${item.qty}`)
    .join(", ");
}

function renderCart() {
  let total = 0;
  summaryEl.innerHTML = "";

  Object.entries(cart).forEach(([name, item]) => {
    total += item.qty * item.price;

    const row = document.createElement("div");
    row.classList.add("cart-row");

    const itemName = document.createElement("span");
    itemName.textContent = name;

    const controls = document.createElement("div");
    controls.classList.add("cart-controls");

    const minusButton = document.createElement("button");
    minusButton.type = "button";
    minusButton.textContent = "-";
    minusButton.setAttribute("aria-label", `Remove one ${name}`);
    minusButton.addEventListener("click", () => changeQty(name, -1));

    const qtyText = document.createElement("span");
    qtyText.textContent = item.qty;

    const plusButton = document.createElement("button");
    plusButton.type = "button";
    plusButton.textContent = "+";
    plusButton.setAttribute("aria-label", `Add one ${name}`);
    plusButton.addEventListener("click", () => changeQty(name, 1));

    controls.append(minusButton, qtyText, plusButton);
    row.append(itemName, controls);
    summaryEl.appendChild(row);
  });

  if (Object.keys(cart).length === 0) {
    summaryEl.innerHTML = '<p class="empty-cart">No items selected yet.</p>';
  }

  const summaryText = buildOrderSummaryText();

  totalEl.textContent = `$${total}`;
  totalInput.value = total;
  summaryInput.value = summaryText;
  cartItemsInput.value = JSON.stringify(cart);
}

function changeQty(name, change) {
  if (!cart[name]) return;

  cart[name].qty += change;

  if (cart[name].qty <= 0) {
    delete cart[name];
  }

  renderCart();
}

document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
  button.addEventListener("click", () => {
    const item = button.closest(".menu-item");
    const name = item.dataset.name;
    const price = Number(item.dataset.price);

    if (!cart[name]) {
      cart[name] = {
        qty: 0,
        price: price,
      };
    }

    cart[name].qty++;
    renderCart();
  });
});

renderCart();

flatpickr("#pickupDate", {
  dateFormat: "Y-m-d",
  altInput: true,
  altFormat: "l, F j, Y",
  minDate: "today",
  maxDate: new Date().fp_incr(60),

  disable: [
    function (date) {
      return date.getDay() !== 0 && date.getDay() !== 6;
    },
  ],
});

form.addEventListener("submit", function (e) {
  const cartItems = Object.keys(cart);

  const selectedPickupTime = document.querySelector(
    'input[name="pickup_time"]:checked',
  );

  if (!cartItems.length) {
    e.preventDefault();
    alert("Please add at least one item to your cart.");
    return;
  }

  if (!pickupDateInput.value) {
    e.preventDefault();
    alert("Please select a pickup date.");
    return;
  }

  if (!selectedPickupTime) {
    e.preventDefault();
    alert("Please select a pickup time.");
    return;
  }
});

const isAcceptingOrders = true;

if (!isAcceptingOrders) {
  form.style.opacity = "0.5";
  form.style.pointerEvents = "none";

  const banner = document.getElementById("announcementBanner");
  banner.textContent =
    "⚠️ We're currently not taking orders until further notice.";
}

const params = new URLSearchParams(window.location.search);
const error = params.get("error");
const errorMessageTop = document.getElementById("orderErrorMessageTop");
const errorMessageBottom = document.getElementById("orderErrorMessage");

function showOrderError(message, consoleMessage) {
  [errorMessageBottom, errorMessageTop].forEach((element) => {
    if (!element) return;

    element.textContent = message;
    element.classList.add("show");
  });

  form.classList.add("form-error");

  errorMessageTop?.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });

  console.error(consoleMessage);
}

// Display error messages based on the error query parameter
if (errorMessageTop || errorMessageBottom) {
  if (error === "didnotpost") {
    showOrderError(
      "Please complete all required fields before submitting your order.",
      "Order form submission was missing required information.",
    );
  }

  if (error === "invalidemail") {
    showOrderError(
      "Please enter a valid email address before submitting your order.",
      "Invalid email address submitted.",
    );
  }

  if (error === "invalidproduct") {
    showOrderError(
      "One or more selected products were invalid. Please try again.",
      "Invalid product submitted.",
    );
  }

  if (error === "invaliddate") {
    showOrderError(
      "Please select a valid weekend pickup date.",
      "Invalid pickup date.",
    );
  }

  if (error === "loaves") {
    showOrderError(
      "Sorry, we have reached the loaf limit for that pickup date. Please choose another pickup date or remove loaf items.",
      "Loaf inventory limit reached for selected pickup date.",
    );
  }

  if (error === "bagels") {
    showOrderError(
      "Sorry, we have reached the bagel limit for that pickup date. Please choose another pickup date or remove bagel items.",
      "Bagel inventory limit reached for selected pickup date.",
    );
  }

  if (error === "database") {
    showOrderError(
      "Something went wrong while saving your order. Please try again.",
      "Database error occurred while saving the order.",
    );
  }
}
