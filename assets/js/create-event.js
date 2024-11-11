document.addEventListener('DOMContentLoaded', () => {
    // Validation du code postal
    const postalInput = document.getElementById('event-postal');
    
    // Modification du type d'input pour n'accepter que des chiffres
    postalInput.setAttribute('type', 'tel');
    postalInput.setAttribute('inputmode', 'numeric');
    postalInput.setAttribute('pattern', '[0-9]*');
    
    postalInput.addEventListener('keypress', (e) => {
        // Bloque tout ce qui n'est pas un chiffre
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
        // Bloque si on dépasse 5 chiffres
        if (postalInput.value.length >= 5 && e.key !== 'Backspace') {
            e.preventDefault();
        }
    });

    postalInput.addEventListener('input', (e) => {
        // Force la valeur à ne contenir que des chiffres
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 5);
    });

    // Validation du prix
    const priceInput = document.getElementById('event-price');
    
    // Modification du type d'input pour n'accepter que des nombres
    priceInput.setAttribute('type', 'number');
    priceInput.setAttribute('step', '0.01');
    priceInput.setAttribute('min', '0');
    
    priceInput.addEventListener('keypress', (e) => {
        // N'autorise que les chiffres et le point
        if (!/[0-9.]/.test(e.key)) {
            e.preventDefault();
        }
        // Empêche un deuxième point
        if (e.key === '.' && priceInput.value.includes('.')) {
            e.preventDefault();
        }
    });

    priceInput.addEventListener('input', (e) => {
        // Ne garde que les chiffres et un seul point
        let value = e.target.value.replace(/[^\d.]/g, '');
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        e.target.value = value;
    });
}); 