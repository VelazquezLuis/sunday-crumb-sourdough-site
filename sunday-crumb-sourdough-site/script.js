const orderCheckboxes = Array.from(document.querySelectorAll('input[name="order_items[]"]'));
const summaryEl = document.getElementById('orderSummary');
const totalEl = document.getElementById('orderTotal');
const totalInput = document.getElementById('orderTotalInput');
const summaryInput = document.getElementById('orderItemsSummaryInput');
const form = document.getElementById('orderForm');

function updateOrderSummary() {
  const selections = orderCheckboxes
    .filter(box => box.checked)
    .map(box => ({ label: box.value, price: Number(box.dataset.price || 0) }));

  const total = selections.reduce((sum, item) => sum + item.price, 0);

  if (!selections.length) {
    summaryEl.innerHTML = '<p>No items selected yet.</p>';
    summaryInput.value = '';
  } else {
    summaryEl.innerHTML = '<ul>' + selections.map(item => `<li>${item.label}</li>`).join('') + '</ul>';
    summaryInput.value = selections.map(item => item.label).join(', ');
  }

  totalEl.textContent = `$${total}`;
  totalInput.value = `$${total}`;
}

orderCheckboxes.forEach(box => box.addEventListener('change', updateOrderSummary));
updateOrderSummary();

form.addEventListener('submit', function (e) {
  const checkedItems = orderCheckboxes.filter(box => box.checked);
  if (!checkedItems.length) {
    e.preventDefault();
    alert('Please select at least one item to order.');
    return;
  }
});
