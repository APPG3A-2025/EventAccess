document.addEventListener("DOMContentLoaded", () => {
	// Initialisation de l'autocomplétion des villes
	const cityInput = document.getElementById("event-city")
	const citySuggestions = document.getElementById("city-suggestions")

	if (cityInput && citySuggestions) {
		new CityAutocomplete(cityInput, citySuggestions)
	}

	// Validation du code postal
	const postalInput = document.getElementById("event-postal")

	// Modification du type d'input pour n'accepter que des chiffres
	postalInput.setAttribute("type", "tel")
	postalInput.setAttribute("inputmode", "numeric")
	postalInput.setAttribute("pattern", "[0-9]*")

	postalInput.addEventListener("keypress", e => {
		// Bloque tout ce qui n'est pas un chiffre
		if (!/[0-9]/.test(e.key)) {
			e.preventDefault()
		}
		// Bloque si on dépasse 5 chiffres
		if (postalInput.value.length >= 5 && e.key !== "Backspace") {
			e.preventDefault()
		}
	})

	postalInput.addEventListener("input", e => {
		// Force la valeur à ne contenir que des chiffres
		e.target.value = e.target.value.replace(/\D/g, "").slice(0, 5)
	})
})

document.addEventListener('DOMContentLoaded', async () => {
	const token = localStorage.getItem('userToken');
	
	if (!token) {
		showNotification('Vous devez être connecté pour créer un événement', 'error');
		return;
	}

	const form = document.getElementById('create-event-form');
	
	// Validation du prix
	const priceInput = document.getElementById('prix');
	if (priceInput) {
		priceInput.setAttribute('type', 'number');
		priceInput.setAttribute('step', '0.01');
		priceInput.setAttribute('min', '0');

		priceInput.addEventListener('keypress', e => {
			// N'autorise que les chiffres et le point
			if (!/[0-9.]/.test(e.key)) {
				e.preventDefault();
			}
			// Empêche un deuxième point
			if (e.key === '.' && priceInput.value.includes('.')) {
				e.preventDefault();
			}
		});

		priceInput.addEventListener('input', e => {
			// Ne garde que les chiffres et un seul point
			let value = e.target.value.replace(/[^\d.]/g, '');
			const parts = value.split('.');
			if (parts.length > 2) {
				value = parts[0] + '.' + parts.slice(1).join('');
			}
			e.target.value = value;
		});
	}

	// Validation du nombre de places
	const placesInput = document.getElementById('places');
	if (placesInput) {
		placesInput.setAttribute('type', 'number');
		placesInput.setAttribute('min', '1');

		placesInput.addEventListener('keypress', e => {
			// N'autorise que les chiffres
			if (!/[0-9]/.test(e.key)) {
				e.preventDefault();
			}
		});

		placesInput.addEventListener('input', e => {
			// Force la valeur à être un nombre positif
			let value = parseInt(e.target.value);
			if (value < 1) {
				e.target.value = 1;
			}
		});
	}

	// Validation de la date
	const dateInput = document.getElementById('date');
	if (dateInput) {
		// Définir la date minimum à aujourd'hui
		const today = new Date().toISOString().split('T')[0];
		dateInput.setAttribute('min', today);
	}

	if (form) {
		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			try {
				const formData = new FormData(form);

				const response = await fetch('../assets/php/create_event.php', {
					method: 'POST',
					headers: {
						'Authorization': `Bearer ${token}`
					},
					body: formData
				});

				const data = await response.json();

				if (data.success) {
					showNotification('Événement créé avec succès !', 'success');
					setTimeout(() => {
						window.location.href = 'profile-organizer.html';
					}, 2000);
				} else {
					showNotification(data.message || 'Erreur lors de la création de l\'événement', 'error');
				}
			} catch (error) {
				console.error('Erreur:', error);
				showNotification('Erreur lors de la création de l\'événement', 'error');
			}
		});
	}
});

function showNotification(message, type = 'error') {
	const notification = document.createElement('div');
	notification.className = `notification ${type}`;
	notification.textContent = message;
	document.body.appendChild(notification);
	
	setTimeout(() => {
		notification.remove();
	}, 3000);
}
