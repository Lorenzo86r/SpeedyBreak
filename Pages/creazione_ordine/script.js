let cart = [];

function addToCart(name, price) {
    const existingItem = cart.find(item => item.name === name);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ name, price, quantity: 1 });
    }
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cart-items');
    const totalEl = document.getElementById('total');

    cartItems.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const li = document.createElement('li');
        li.textContent = `${item.name} x${item.quantity} - €${(item.price * item.quantity).toFixed(2)}`;
        cartItems.appendChild(li);
        total += item.price * item.quantity;
    });

    totalEl.textContent = `Totale: €${total.toFixed(2)}`;
}

function checkout() {
    if (cart.length === 0) {
        alert("Il carrello è vuoto!");
        return;
    }
    alert("Ordine confermato!\nGrazie per aver ordinato.");
    cart = [];
    renderCart();
}