
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const toggleButton = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                
                // Add overlay for mobile
                if (sidebar.classList.contains('show')) {
                    const overlay = document.createElement('div');
                    overlay.classList.add('sidebar-overlay');
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.backgroundColor = 'rgba(0,0,0,0.4)';
                    overlay.style.zIndex = '1020';
                    document.body.appendChild(overlay);
                    
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        this.remove();
                    });
                } else {
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                }
            });
            
            // Close sidebar when a menu item is clicked on mobile
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        const overlay = document.querySelector('.sidebar-overlay');
                        if (overlay) {
                            overlay.remove();
                        }
                    }
                });
            });
        }
        
        // Check screen size on resize and handle sidebar accordingly
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
