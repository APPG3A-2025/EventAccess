document.addEventListener('DOMContentLoaded', () => {
	// Récupérer les paramètres de l'URL
	const urlParams = new URLSearchParams(window.location.search);
	const searchQuery = urlParams.get('query');

	// Remplir les champs avec les valeurs de recherche
	const searchInput = document.getElementById('search-input');
	const searchQueryDisplay = document.getElementById('search-query');

	if (searchInput && searchQuery) {
		searchInput.value = searchQuery;
	}

	// Afficher les termes de recherche
	if (searchQueryDisplay) {
		searchQueryDisplay.textContent = searchQuery || '';
	}

	// Initialisation de l'autocomplétion des villes
	const cityInput = document.getElementById('city-input');
	const citySuggestions = document.getElementById('city-suggestions');

	if (cityInput && citySuggestions) {
		console.log('Initialisation de l\'autocomplétion des villes');
		new CityAutocomplete(cityInput, citySuggestions);
	} else {
		console.log('Éléments non trouvés:', { cityInput, citySuggestions });
	}

	// Récupérer aussi le paramètre ville de l'URL
	const cityQuery = urlParams.get('city');
	if (cityInput && cityQuery) {
		cityInput.value = cityQuery;
	}

	// Mise à jour de l'affichage des termes de recherche
	if (searchQueryDisplay) {
		let displayText = searchQuery || '';
		if (cityQuery) {
			displayText += cityQuery ? ` à ${cityQuery}` : '';
		}
		searchQueryDisplay.textContent = displayText;
	}

	// Simuler des résultats de recherche
	const mockResults = [
		{
			id: 1,
			title: "Concert de Jazz",
			date: "2024-03-15",
			location: "Le Grand Théâtre, Paris",
			image: "https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3",
			price: "25€"
		},
		{
			id: 2,
			title: "Festival de Rock",
			date: "2024-03-20",
			location: "Parc des Expositions, Lyon",
			image: "https://images.unsplash.com/photo-1470225620780-dba8ba36b745",
			price: "45€"
		},
		{
			id: 3,
			title: "Théâtre Contemporain",
			date: "2024-03-25",
			location: "Théâtre Municipal, Marseille",
			image: "https://images.unsplash.com/photo-1507924538820-ede94a04019d",
			price: "30€"
		}
	];

	// Afficher les résultats
	const resultsGrid = document.getElementById('results-grid');
	if (resultsGrid) {
		resultsGrid.innerHTML = mockResults.map(result => `
            <div class="result-card">
                <img src="${result.image}" alt="${result.title}" class="result-image">
                <div class="result-content">
                    <div class="result-date">${result.date}</div>
                    <h3 class="result-title">${result.title}</h3>
                    <div class="result-location">${result.location}</div>
                    <div class="result-price">${result.price}</div>
                </div>
            </div>
        `).join('');
	}
});