let cart = [];

function addToCart(name, price) {
    const item = cart.find(p => p.name === name);

    if (item) {
        item.quantity++;
    } else {
        cart.push({
            name: name,
            price: price,
            quantity: 1
        });
    }

    updateCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateCart() {
    const cartList = document.getElementById("cart-list");
    const totalText = document.getElementById("total");

    cartList.innerHTML = "";
    let total = 0;

    cart.forEach((item, index) => {
        const li = document.createElement("li");
        li.innerHTML = `
            ${item.name} x${item.quantity} - €${(item.price * item.quantity).toFixed(2)}
            <button onclick="removeFromCart(${index})">❌</button>
        `;
        cartList.appendChild(li);
        total += item.price * item.quantity;
    });

    totalText.textContent = "Totale: €" + total.toFixed(2);
}

function sendOrder() {
    if (cart.length === 0) {
        alert("Il carrello è vuoto!");
        return;
    }

    fetch("../DataBase/orders.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(cart)
    })
        .then(res => res.text())
        .then(data => {
            alert("Ordine inviato con successo!");
            cart = [];
            updateCart();
        })
        .catch(err => {
            console.error(err);
            alert("Errore nell'invio dell'ordine");
        });
}