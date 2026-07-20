const orderCheckboxes = Array.from(
  document.querySelectorAll('input[name="order_items[]"]'),
);

const summaryEl = document.getElementById("orderSummary");
const totalEl = document.getElementById("orderTotal");
const totalInput = document.getElementById("orderTotalInput");
const summaryInput = document.getElementById("orderItemsSummaryInput");
const form = document.getElementById("orderForm");
const pickupDateInput = document.getElementById("pickupDate");

function updateOrderSummary() {
  const selections = orderCheckboxes
    .filter((box) => box.checked)
    .map((box) => ({
      label: box.value,
      price: Number(box.dataset.price || 0),
    }));

  const total = selections.reduce((sum, item) => sum + item.price, 0);

  if (!selections.length) {
    summaryEl.innerHTML = "<p>No items selected yet.</p>";
    summaryInput.value = "";
  } else {
    summaryEl.innerHTML =
      "<ul>" +
      selections.map((item) => `<li>${item.label}</li>`).join("") +
      "</ul>";

    summaryInput.value = selections.map((item) => item.label).join(", ");
  }

  totalEl.textContent = `$${total}`;
  totalInput.value = `$${total}`;
}

orderCheckboxes.forEach((box) =>
  box.addEventListener("change", updateOrderSummary),
);

updateOrderSummary();

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
  const checkedItems = orderCheckboxes.filter((box) => box.checked);

  const selectedPickupTime = document.querySelector(
    'input[name="pickup_time"]:checked',
  );

  if (!checkedItems.length) {
    e.preventDefault();
    alert("Please select at least one item to order.");
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
  const form = document.getElementById("orderForm");

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

console.log("Top:", errorMessageTop);
console.log("Bottom:", errorMessageBottom);


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
