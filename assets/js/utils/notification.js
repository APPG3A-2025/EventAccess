export function showNotification(message, type = 'error') {
    const notification = document.getElementById('notification');
    const messageElement = notification.querySelector('.notification-message');
    messageElement.textContent = message;
    
    notification.className = 'notification';
    notification.classList.add('show', type);
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 5000);
} 