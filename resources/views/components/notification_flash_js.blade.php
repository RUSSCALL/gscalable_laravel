<!-- components/notification_flash_js.blade.php -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss flash messages after 5 seconds
        const flashMessages = document.querySelectorAll('#flash-message');
        
        flashMessages.forEach(function(flashMessage) {
            // Create a Bootstrap alert instance
            const alert = new bootstrap.Alert(flashMessage);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                alert.close();
            }, 5000);
            
            // Add slide-out animation before removing from DOM
            flashMessage.addEventListener('close.bs.alert', function(e) {
                e.preventDefault();
                const alertEl = e.target;
                alertEl.style.transition = 'all 0.5s ease-in-out';
                alertEl.style.opacity = '0';
                alertEl.style.transform = 'translateX(100%)';
                
                setTimeout(function() {
                    alert.dispose();
                    alertEl.remove();
                }, 500);
            });
        });
    });
    
    // Function to show flash messages programmatically
    function showFlashMessage(type, message) {
        const flashNotifications = document.querySelector('.flash-notifications');
        
        if (!flashNotifications) return;
        
        // Create icon based on type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '<i class="bi bi-check-circle-fill me-2"></i>';
                break;
            case 'danger':
            case 'error':
                icon = '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
                type = 'danger';
                break;
            case 'warning':
                icon = '<i class="bi bi-exclamation-circle-fill me-2"></i>';
                break;
            case 'info':
                icon = '<i class="bi bi-info-circle-fill me-2"></i>';
                break;
            default:
                icon = '<i class="bi bi-bell-fill me-2"></i>';
        }
        
        // Create the alert element
        const alertEl = document.createElement('div');
        alertEl.id = 'flash-message';
        alertEl.className = `alert alert-${type} alert-dismissible fade show`;
        alertEl.setAttribute('role', 'alert');
        alertEl.innerHTML = `
            ${icon}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add the alert to the notifications container
        flashNotifications.appendChild(alertEl);
        
        // Create Bootstrap alert instance
        const alert = new bootstrap.Alert(alertEl);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            if (alertEl.parentNode) {
                alert.close();
            }
        }, 5000);
        
        // Add slide-out animation before removing from DOM
        alertEl.addEventListener('close.bs.alert', function(e) {
            e.preventDefault();
            const target = e.target;
            target.style.transition = 'all 0.5s ease-in-out';
            target.style.opacity = '0';
            target.style.transform = 'translateX(100%)';
            
            setTimeout(function() {
                alert.dispose();
                target.remove();
            }, 500);
        });
    }
</script>