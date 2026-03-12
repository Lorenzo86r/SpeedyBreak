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
                    <button class="qty-btn" onclick="increaseQuantity(${index})" ${item.quantity >= 5 ? 'disabled' : ''}>
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
    if (cart[index].quantity < 5) {
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



function sendOrder(){
    // carrello vuoto
    if(cart.length === 0){
        alert("Carrello vuoto");
        return;
    }

    fetch("ordine.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },

        body:JSON.stringify(cart)

    })

        .then(res=>res.text())

        .then(data=>{
            alert("Ordine inviato!");
            cart = [];
            updateCart();
        })

        .catch(err=>{
            alert("Errore invio ordine");
            console.error(err);
        });
}