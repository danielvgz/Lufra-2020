// Simple cart implementation using localStorage
document.addEventListener('DOMContentLoaded', function(){
  const CART_KEY = 'site_cart_v1';
  const cartBtn = document.getElementById('cartBtn');
  const cartCount = document.getElementById('cartCount');
  const cartModalEl = document.getElementById('cartModal');
  const cartItemsEl = document.getElementById('cartItems');
  const cartEmpty = document.getElementById('cartEmpty');
  const cartTotalEl = document.getElementById('cartTotal');

  function getCart(){
    try{
      return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    }catch(e){return []}
  }

  function saveCart(cart){
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    renderBadge();
  }

  function renderBadge(){
    const cart = getCart();
    const qty = cart.reduce((s,i)=>s+Number(i.qty||1),0);
    if(cartCount) cartCount.textContent = qty;
  }

  function addToCart(item){
    const cart = getCart();
    const exists = cart.find(i=>i.id===item.id);
    if(exists){ exists.qty = Number(exists.qty||1)+1; }
    else { cart.push(Object.assign({qty:1}, item)); }
    saveCart(cart);
  }

  function renderCart(){
    const cart = getCart();
    cartItemsEl.innerHTML = '';
    if(!cart.length){ cartEmpty.style.display='block'; cartItemsEl.style.display='none'; cartTotalEl.textContent='€0.00'; return; }
    cartEmpty.style.display='none'; cartItemsEl.style.display='block';
    let total = 0;
    cart.forEach(it=>{
      const price = Number(it.price || 0);
      const qty = Number(it.qty || 1);
      total += price * qty;
      const row = document.createElement('div');
      row.className = 'd-flex align-items-center gap-3 mb-3';
      row.innerHTML = `
        <img src="${it.image||''}" style="width:80px;height:60px;object-fit:cover" class="rounded">
        <div class="flex-grow-1">
          <div><strong>${escapeHtml(it.name)}</strong></div>
          <div class="text-muted small">€${price.toFixed(2)} x <input type="number" min="1" value="${qty}" data-id="${it.id}" class="form-control form-control-sm d-inline-block ms-2 qty-input" style="width:80px"></div>
        </div>
        <div><button class="btn btn-sm btn-outline-danger remove-item" data-id="${it.id}"><i class="bi bi-trash"></i></button></div>
      `;
      cartItemsEl.appendChild(row);
    });
    cartTotalEl.textContent = '€'+total.toFixed(2);
  }

  function escapeHtml(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  // Delegated listeners
  document.body.addEventListener('click', function(e){
    const t = e.target.closest('.add-to-cart');
    if(t){
      const item = {
        id: t.getAttribute('data-id'),
        name: t.getAttribute('data-name'),
        price: Number(t.getAttribute('data-price')||0),
        image: t.getAttribute('data-image')
      };
      addToCart(item);
      // visual feedback
      t.innerHTML = '✓';
      setTimeout(()=> t.innerHTML = 'Añadir', 700);
    }

    const rem = e.target.closest('.remove-item');
    if(rem){
      const id = rem.getAttribute('data-id');
      let cart = getCart();
      cart = cart.filter(i=>i.id!==id);
      saveCart(cart);
      renderCart();
    }
  });

  // quantity change
  document.body.addEventListener('change', function(e){
    if(e.target && e.target.classList.contains('qty-input')){
      const id = e.target.getAttribute('data-id');
      const val = Number(e.target.value||1);
      const cart = getCart();
      const it = cart.find(i=>i.id===id);
      if(it){ it.qty = val; saveCart(cart); renderCart(); }
    }
  });

  // cart modal open
  if(cartBtn && cartModalEl){
    const cartModal = new bootstrap.Modal(cartModalEl);
    cartBtn.addEventListener('click', function(){ renderCart(); cartModal.show(); });
  }

  // checkout handler (demo)
  const checkoutBtn = document.getElementById('checkoutBtn');
  if(checkoutBtn){
    checkoutBtn.addEventListener('click', function(){
      // Simple demo behaviour
      const cart = getCart();
      if(!cart.length){ alert('El carrito está vacío.'); return; }
      localStorage.removeItem(CART_KEY);
      renderBadge();
      renderCart();
      alert('Compra simulada: Gracias por tu pedido.');
      const modal = bootstrap.Modal.getInstance(cartModalEl);
      if(modal) modal.hide();
    });
  }

  // initialize badge
  renderBadge();

});
