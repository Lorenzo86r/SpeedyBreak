let cart = [];

function addToCart(name, price) {
    const item = cart.find(function (p) {
        return p.name === name;
    });

    if (item) {
        if (item.quantity < 30) item.quantity++;
        else {
            alert("Puoi aggiungere al massimo 30 pezzi per articolo");
            return;
        }
    }
    else {
        cart.push({
            name: name,
            price: price,
            quantity: 1
        });
    }

    updateCart();
}

function removeFromCart(index){
    cart.splice(index,1);
    updateCart();
}

function updateCart(){

    const list = document.getElementById("cart-list");
    const totalText = document.getElementById("total");
    const floatingBtn = document.getElementById("floating-cart-btn");
    const floatingCount = document.getElementById("floating-cart-count");
    const floatingTotal = document.getElementById("floating-cart-total");

    list.innerHTML="";
    let total = 0;
    let totalItems = 0;
    
    if (cart.length === 0) {
        list.innerHTML = `
            <div class="empty-cart-msg">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.2; margin-bottom: 20px;">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    <line x1="12" y1="10" x2="12" y2="10.01"></line>
                </svg>
                <h3 style="color: var(--color-secondary); font-size: var(--font-size-xl); margin-bottom: 8px;">Il carrello è vuoto</h3>
                <span>Aggiungi dei favolosi panini dal menu!</span>
                <button class="btn btn-secondary mt-4" onclick="toggleCartOverlay()">Torna al Menu</button>
            </div>
        `;
        totalText.textContent = "€0.00";
        floatingBtn.style.display = 'none'; // Nascondi pulsante se carrello vuoto
        return;
    }

    cart.forEach((item,index)=>{
        const li = document.createElement("li");
        li.className = "cart-item";

        li.innerHTML = `
            <div class="cart-item-info">
                <span class="cart-item-name" style="font-size: var(--font-size-md);">${item.name}</span>
                <span class="cart-item-price" style="font-size: var(--font-size-md);">€${(item.price*item.quantity).toFixed(2)}</span>
            </div>
            
            <div class="cart-item-controls">
                <div class="qty-control" style="transform: scale(1.2); transform-origin: right;">
                    <button class="qty-btn" onclick="decreaseQuantity(${index})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <span class="qty-value">${item.quantity}</span>
                    <button class="qty-btn" onclick="increaseQuantity(${index})" ${item.quantity >= 30 ? 'disabled' : ''}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                </div>
                
                <button class="cart-item-remove" style="margin-left: 20px; width: 36px; height: 36px;" onclick="removeFromCart(${index})" title="Rimuovi dal carrello">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </div>
        `;
        list.appendChild(li);
        total += item.price * item.quantity;
        totalItems += item.quantity;
    });
    
    // Aggiorna totali
    const formattedTotal = "€" + total.toFixed(2);
    totalText.textContent = formattedTotal;
    
    // Mostra/aggiorna pulsante fluttuante
    floatingCount.textContent = totalItems;
    floatingTotal.textContent = formattedTotal;
    floatingBtn.style.display = 'flex';
}

function increaseQuantity(index) {
    if (cart[index].quantity < 30) {
        cart[index].quantity++;
        updateCart();
    }
}

function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        updateCart();
    } else {
        removeFromCart(index); // Remove if asking to decrease from 1
    }
}

// Funzione overlay
function toggleCartOverlay() {
    const overlay = document.getElementById("cart-overlay");
    overlay.classList.toggle("active");
    if(overlay.classList.contains("active")) {
        // block body scroll
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Initial call to update cart empty state
document.addEventListener('DOMContentLoaded', updateCart);



function sendOrder(){
    // carrello vuoto
    if(cart.length === 0){
        alert("Carrello vuoto");
        return;
    }
    
    // fetch payment method and note logic would go here to include in request optionally
    // Example: const method = document.querySelector('input[name="payment-method"]:checked').value;
    // Example: const note = document.getElementById('order-note').value;

    fetch("ordine.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        // currently sends just cart array, api must be updated to accept method & note later if so
        body:JSON.stringify(cart)

    })
        .then(res=>res.text())
        .then(data=>{
            alert("Ordine inviato con successo!");
            cart = [];
            document.getElementById('order-note').value = ""; // reset notes
            toggleCartOverlay(); // Chiudi overlay
            updateCart();
        })
        .catch(err=>{
            alert("Errore invio ordine");
            console.error(err);
        });
}