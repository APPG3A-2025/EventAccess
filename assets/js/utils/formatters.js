export function formatPhoneNumber(input) {
    input.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 9) value = value.slice(0, 9);

        let formattedValue = '';
        if (value.length > 0) {
            formattedValue = value[0];
            for (let i = 1; i < value.length; i += 2) {
                formattedValue += ' ' + value.slice(i, i + 2);
            }
        }
        
        e.target.value = formattedValue;
    });
}

export function formatDate(dateStr) {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('fr-FR', options);
} 