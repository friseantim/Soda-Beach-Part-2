// Show / hide password toggle
document.querySelectorAll('.pw-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('input');
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.classList.toggle('on', input.type === 'text');
  });
});

// Booking form: live availability calculations + dynamic total updates
const bf = document.getElementById('booking-form');
if (bf) {
  const date = bf.querySelector('[name=visit_date]');
  const vis = bf.querySelector('[name=visitors]');
  const qty = bf.querySelector('[name=cottage_qty]');
  const radios = bf.querySelectorAll('[name=cottage_id]');
  const pass = parseFloat(bf.dataset.pass);
  const peso = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });

  const total = () => {
    let c = 0;
    const sel = bf.querySelector('[name=cottage_id]:checked');
    if (sel && sel.value !== '0') c = parseFloat(sel.dataset.price) * (parseInt(qty.value) || 1);
    document.getElementById('total').textContent = peso((parseInt(vis.value) || 0) * pass + c);
  };

  const refresh = async () => {
    if (!date.value) return;
    try {
      const a = await (await fetch(bf.dataset.base + '/availability.php?date=' + date.value)).json();
      document.getElementById('slots').textContent = a.slots_left + ' day-pass slots left on this date';
      vis.max = Math.min(50, a.slots_left);
      radios.forEach(r => {
        if (r.value === '0') return;
        const left = a.cottages[r.value] ?? 0;
        document.getElementById('left-' + r.value).textContent = ' (' + left + ' left)';
        r.disabled = left <= 0;
        if (r.disabled && r.checked) bf.querySelector('[name=cottage_id][value="0"]').checked = true;
      });
    } catch (e) {}
    total();
  };

  date.addEventListener('change', refresh);
  [vis, qty].forEach(i => i.addEventListener('input', total));
  radios.forEach(r => r.addEventListener('change', total));
  refresh();
}