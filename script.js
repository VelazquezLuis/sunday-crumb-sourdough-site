
const orderCheckboxes = Array.from(
  document.querySelectorAll('input[name="order_items[]"]')
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
  box.addEventListener("change", updateOrderSummary)
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
    'input[name="pickup_time"]:checked'
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

/*
|--------------------------------------------------------------------------
| ERROR MODAL
|--------------------------------------------------------------------------
| Display inventory and database errors in a dismissible popup.
|--------------------------------------------------------------------------
*/

const params = new URLSearchParams(window.location.search);
const error = params.get("error");

const modal = document.getElementById("errorModal");
const modalMessage = document.getElementById("modalMessage");
const closeModal = document.getElementById("closeModal");

let message = "";

if (error === "loaves") {
  message =
    "Sorry, we have reached the loaf limit for that pickup date. Please select another pickup date or remove loaf items.";
}

if (error === "bagels") {
  message =
    "Sorry, we have reached the bagel limit for that pickup date. Please select another pickup date or remove bagel items.";
}

if (error === "database") {
  message =
    "Something went wrong while processing your order. Please try again.";
}

if (message) {
  modalMessage.textContent = message;
  modal.classList.add("show");
}

/*
|--------------------------------------------------------------------------
| CLOSE MODAL BUTTON
|--------------------------------------------------------------------------
*/
closeModal.addEventListener("click", () => {
  modal.classList.remove("show");
});

/*
|--------------------------------------------------------------------------
| CLOSE MODAL WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/
modal.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.classList.remove("show");
  }
});