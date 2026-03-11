let cart = [];

function addToCart(name, price) {
    const item = cart.find(function (p) {
        return p.name === name;
    });

    if (item) {
        if (item.quantity < 5) item.quantity++;
        else {
            alert("Puoi aggiungere al massimo 5 pezzi per articolo");
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
    cart.forEach((item,index)=>{

        const li = document.createElement("li");

        li.innerHTML = `
${item.name} x${item.quantity} - €${(item.price*item.quantity).toFixed(2)}
<button onclick="removeFromCart(${index})">❌</button>
`;
        list.appendChild(li);
        total += item.price * item.quantity;
    });
    totalText.textContent = "Totale: €"+total.toFixed(2);
}



function sendOrder(){
    // controllo accesso utente
    if (!isLoggedIn) {
        alert("Devi effettuare il login per ordinare!");
        window.location.href = "../auth/login.php"; // reindirizza al login
        return;
    }

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