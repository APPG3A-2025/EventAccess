class CityAutocomplete {
    constructor(inputElement, suggestionsElement) {
        this.input = inputElement;
        this.suggestions = suggestionsElement;
        this.cities = [
            { nom: "Paris", codesPostaux: ["75000"] },
            { nom: "Marseille", codesPostaux: ["13000"] },
            { nom: "Lyon", codesPostaux: ["69000"] },
            { nom: "Toulouse", codesPostaux: ["31000"] },
            { nom: "Nice", codesPostaux: ["06000"] },
            { nom: "Nantes", codesPostaux: ["44000"] },
            { nom: "Montpellier", codesPostaux: ["34000"] },
            { nom: "Strasbourg", codesPostaux: ["67000"] },
            { nom: "Bordeaux", codesPostaux: ["33000"] },
            { nom: "Lille", codesPostaux: ["59000"] },
            { nom: "Rennes", codesPostaux: ["35000"] },
            { nom: "Reims", codesPostaux: ["51100"] },
            { nom: "Toulon", codesPostaux: ["83000"] },
            { nom: "Grenoble", codesPostaux: ["38000"] },
            { nom: "Dijon", codesPostaux: ["21000"] }
        ];
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.input.addEventListener('input', () => {
            const searchTerm = this.input.value.trim().toLowerCase();
            if (searchTerm.length > 0) {
                this.showSuggestions(searchTerm);
            } else {
                this.hideSuggestions();
            }
            this.validateCity();
        });

        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.suggestions.contains(e.target)) {
                this.hideSuggestions();
            }
        });

        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideSuggestions();
            }
        });

        const form = this.input.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.validateCity()) {
                    e.preventDefault();
                    this.showError('Veuillez sélectionner une ville valide');
                }
            });
        }
    }

    validateCity() {
        const inputValue = this.input.value.trim();
        const isValid = this.cities.some(city => city.nom === inputValue);
        
        if (isValid) {
            this.input.classList.remove('error');
            this.hideError();
            return true;
        } else {
            this.input.classList.add('error');
            return false;
        }
    }

    showError(message) {
        let errorDiv = this.input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            this.input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
    }

    hideError() {
        const errorDiv = this.input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    showSuggestions(searchTerm) {
        const matches = this.cities
            .filter(city => city.nom.toLowerCase().includes(searchTerm))
            .slice(0, 5);

        if (matches.length === 0) {
            this.hideSuggestions();
            return;
        }

        this.suggestions.innerHTML = matches
            .map(city => `
                <div class="city-suggestion">
                    <span class="city-name">${city.nom}</span>
                    <span class="city-postal">${city.codesPostaux[0]}</span>
                </div>
            `)
            .join('');

        this.suggestions.style.display = 'block';

        this.suggestions.querySelectorAll('.city-suggestion').forEach((el, index) => {
            el.addEventListener('click', () => {
                this.selectCity(matches[index]);
            });
        });
    }

    selectCity(city) {
        this.input.value = city.nom;
        if (document.getElementById('event-postal')) {
            document.getElementById('event-postal').value = city.codesPostaux[0];
        }
        this.hideSuggestions();
        this.validateCity();
    }

    hideSuggestions() {
        this.suggestions.style.display = 'none';
    }
}

// Initialisation globale
window.CityAutocomplete = CityAutocomplete;