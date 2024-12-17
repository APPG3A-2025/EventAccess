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

	// Validation du prix
	const priceInput = document.getElementById("event-price")

	// Modification du type d'input pour n'accepter que des nombres
	priceInput.setAttribute("type", "number")
	priceInput.setAttribute("step", "0.01")
	priceInput.setAttribute("min", "0")

	priceInput.addEventListener("keypress", e => {
		// N'autorise que les chiffres et le point
		if (!/[0-9.]/.test(e.key)) {
			e.preventDefault()
		}
		// Empêche un deuxième point
		if (e.key === "." && priceInput.value.includes(".")) {
			e.preventDefault()
		}
	})

	priceInput.addEventListener("input", e => {
		// Ne garde que les chiffres et un seul point
		let value = e.target.value.replace(/[^\d.]/g, "")
		const parts = value.split(".")
		if (parts.length > 2) {
			value = parts[0] + "." + parts.slice(1).join("")
		}
		e.target.value = value
	})
})

// Gestion de la soumission du formulaire
document
	.getElementById("create-event-form")
	.addEventListener("submit", function (e) {
		e.preventDefault()

		// Récupération de la date et heure de l'événement
		const eventDate = document.getElementById("event-date").value
		const eventTime = document.getElementById("event-time").value
		const eventDateTime = new Date(`${eventDate}T${eventTime}`)
		const now = new Date()

		// Vérification si la date est dans le futur
		if (eventDateTime <= now) {
			const notification = document.createElement("div")
			notification.className = "notification error"
			notification.innerHTML = `
				<div class="notification-content">
					<i class="fas fa-exclamation-circle"></i>
					<span>La date et l'heure de l'événement doivent être ultérieures à maintenant</span>
				</div>
			`
			document.body.appendChild(notification)
			setTimeout(() => {
				notification.classList.add("show")
			}, 100)
			setTimeout(() => {
				notification.classList.remove("show")
				setTimeout(() => {
					notification.remove()
				}, 1500)
			}, 3000)
			return
		}

		const formData = new FormData(this)

		fetch("/EventAccess/assets/php/creation_evenement.php", {
			method: "POST",
			body: formData,
		})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Créer et afficher la notification
					const notification = document.createElement("div")
					notification.className = "notification success"
					notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-check-circle"></i>
                    <span>${data.message}</span>
                </div>
            `

					document.body.appendChild(notification)

					// Animation d'entrée
					setTimeout(() => {
						notification.classList.add("show")
					}, 100)

					// Supprimer la notification après 3 secondes
					setTimeout(() => {
						notification.classList.remove("show")
						setTimeout(() => {
							notification.remove()
						}, 1500)
					}, 3000)

					// Réinitialiser le formulaire
					e.target.reset()
				} else {
					// Gérer l'erreur
					console.error("Erreur:", data.message)
					const notification = document.createElement("div")
					notification.className = "notification error"
					notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${data.message}</span>
                </div>
            `
					document.body.appendChild(notification)
					setTimeout(() => {
						notification.classList.add("show")
					}, 100)
					setTimeout(() => {
						notification.classList.remove("show")
						setTimeout(() => {
							notification.remove()
						}, 1500)
					}, 3000)
				}
			})
			.catch(error => {
				console.error("Erreur:", error)
				const notification = document.createElement("div")
				notification.className = "notification error"
				notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Une erreur est survenue lors de la création de l'événement</span>
                </div>
            `
				document.body.appendChild(notification)
				setTimeout(() => {
					notification.classList.add("show")
				}, 100)
				setTimeout(() => {
					notification.classList.remove("show")
					setTimeout(() => {
						notification.remove()
					}, 1500)
				}, 3000)
			})
	})
