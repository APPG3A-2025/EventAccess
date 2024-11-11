// Fonctions de validation du mot de passe
export function validatePassword(password) {
    const minLength = password.length >= 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);

    const errors = [];
    if (!minLength) errors.push('Le mot de passe doit contenir au moins 8 caractères');
    if (!hasUpperCase) errors.push('Le mot de passe doit contenir au moins une majuscule');
    if (!hasLowerCase) errors.push('Le mot de passe doit contenir au moins une minuscule');
    if (!hasNumber) errors.push('Le mot de passe doit contenir au moins un chiffre');

    return {
        isValid: minLength && hasUpperCase && hasLowerCase && hasNumber,
        strength: calculatePasswordStrength(password),
        errors: errors
    };
}

export function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

export function validatePhoneNumber(phone) {
    const cleanPhone = phone.replace(/\D/g, '');
    
    if (cleanPhone.length !== 9) {
        return {
            isValid: false,
            error: 'Le numéro doit contenir 9 chiffres'
        };
    }

    if (!['1','2','3','4','5','6','7','9'].includes(cleanPhone[0])) {
        return {
            isValid: false,
            error: 'Numéro invalide'
        };
    }

    return {
        isValid: true,
        error: null
    };
}

export function calculatePasswordStrength(password) {
    let score = 0;

    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (password.length >= 16) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score < 3) return 'weak';
    if (score < 4) return 'medium';
    if (score < 6) return 'strong';
    return 'very-strong';
}