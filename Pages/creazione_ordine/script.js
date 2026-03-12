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

    list.innerHTML="";
    let total = 0;
    
    if (cart.length === 0) {
        list.innerHTML = `
            <div class="empty-cart-msg">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    <line x1="12" y1="10" x2="12" y2="10.01"></line>
                </svg>
                <span>Il tuo carrello è vuoto</span>
            </div>
        `;
        totalText.textContent = "€0.00";
        return;
    }

    cart.forEach((item,index)=>{
        const li = document.createElement("li");
        li.className = "cart-item";

        li.innerHTML = `
            <div class="cart-item-info">
                <span class="cart-item-name">${item.name}</span>
                <span class="cart-item-price">€${(item.price*item.quantity).toFixed(2)}</span>
            </div>
            
            <div class="cart-item-controls">
                <div class="qty-control">
                    <button class="qty-btn" onclick="decreaseQuantity(${index})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <span class="qty-value">${item.quantity}</span>
                    <button class="qty-btn" onclick="increaseQuantity(${index})" ${item.quantity >= 30 ? 'disabled' : ''}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                </div>
                
                <button class="cart-item-remove" onclick="removeFromCart(${index})" title="Rimuovi">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </div>
        `;
        list.appendChild(li);
        total += item.price * item.quantity;
    });
    totalText.textContent = "€"+total.toFixed(2);
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
// Initial call to update cart empty state
document.addEventListener('DOMContentLoaded', updateCart);



function openConfirmModal(){
    // carrello vuoto
    if(cart.length === 0){
        alert("Carrello vuoto");
        return;
    }
    
    // Check if user is logged in
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
        alert("Effettua il login prima di procedere con l'ordine.");
        window.location.href = '../auth/login.php';
        return;
    }

    const modal = document.getElementById('confirm-modal');
    const modalList = document.getElementById('modal-cart-list');
    const modalTotal = document.getElementById('modal-total');
    const modalMethod = document.getElementById('modal-method');
    const modalNote = document.getElementById('modal-note');
    
    // Populate list
    modalList.innerHTML = '';
    let total = 0;
    cart.forEach(item => {
        const li = document.createElement('li');
        li.style.display = 'flex';
        li.style.justifyContent = 'space-between';
        li.style.padding = '4px 0';
        li.style.borderBottom = '1px dashed var(--color-border)';
        
        li.innerHTML = `
            <span><span style="color: var(--color-primary); font-weight: 600; margin-right: 6px;">${item.quantity}x</span> ${item.name}</span>
            <span style="font-weight: 500;">€${(item.price * item.quantity).toFixed(2)}</span>
        `;
        modalList.appendChild(li);
        total += item.price * item.quantity;
    });
    
    modalTotal.textContent = '€' + total.toFixed(2);
    
    // Get values from form
    const methodEl = document.querySelector('input[name="payment-method"]:checked');
    const noteEl = document.getElementById('order-note');
    
    modalMethod.textContent = methodEl ? methodEl.value : '-';
    modalNote.textContent = noteEl && noteEl.value.trim() !== '' ? noteEl.value : 'Nessuna nota';
    
    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function submitConfirmedOrder() {
    const submitBtn = document.getElementById('confirm-submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span style="display:inline-block; animation: spin 1s linear infinite;">⏳</span> Invio in corso...';

    // Get note and method
    const methodEl = document.querySelector('input[name="payment-method"]:checked');
    const noteEl = document.getElementById('order-note');
    
    const payload = {
        cart: cart,
        metodo: methodEl ? methodEl.value : 'Contanti',
        nota: noteEl ? noteEl.value : ''
    };

    fetch("ordine.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify(payload)
    })
    .then(res=>res.json())
    .then(data=>{
        if (data.status === "success") {
            cart = [];
            updateCart();
            window.location.href = `conferma_ordine.php?id_ordine=${data.id_ordine}`;
        } else {
            alert("Errore dal server: " + (data.message || "Sconosciuto"));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: -4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Conferma';
        }
    })
    .catch(err=>{
        alert("Errore invio ordine");
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: -4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Conferma';
    });
}