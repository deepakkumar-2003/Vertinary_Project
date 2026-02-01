// Main JavaScript for Veterinary Management System
// Fully Responsive Mobile-First JavaScript

(function() {
    'use strict';

    // ===========================================
    // MOBILE SIDEBAR TOGGLE
    // ===========================================

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    function openSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            body.style.overflow = 'hidden';
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'true');
            }
        }
    }

    function closeSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            body.style.overflow = '';
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        }
    }

    function toggleSidebar() {
        if (sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Event listeners for sidebar
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });

    // Close sidebar when clicking a link (mobile)
    if (sidebar) {
        const sidebarLinks = sidebar.querySelectorAll('.sidebar-menu a');
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 991) {
                    closeSidebar();
                }
            });
        });
    }

    // Close sidebar on resize to desktop
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 991) {
                closeSidebar();
            }
        }, 100);
    });

    // Make toggleSidebar available globally for legacy support
    window.toggleSidebar = toggleSidebar;

    // ===========================================
    // ALERTS - AUTO DISMISS & CLOSE
    // ===========================================

    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                fadeOutAndRemove(alert);
            }, 5000);
        });

        // Close button functionality
        const closeButtons = document.querySelectorAll('.alert .close-btn');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const alert = this.parentElement;
                fadeOutAndRemove(alert);
            });
        });
    });

    function fadeOutAndRemove(element) {
        if (!element) return;
        element.style.transition = 'opacity 0.3s, transform 0.3s';
        element.style.opacity = '0';
        element.style.transform = 'translateY(-10px)';
        setTimeout(function() {
            if (element.parentNode) {
                element.remove();
            }
        }, 300);
    }

    // ===========================================
    // CONFIRM DELETE
    // ===========================================

    window.confirmDelete = function(message) {
        message = message || 'Are you sure you want to delete this record?';
        return confirm(message);
    };

    // ===========================================
    // FORMAT CURRENCY (Indian Rupees)
    // ===========================================

    window.formatCurrency = function(amount) {
        return '₹' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    // ===========================================
    // FORM VALIDATION
    // ===========================================

    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(function(input) {
            // Remove previous error state
            input.classList.remove('error');

            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            }

            // Email validation
            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    input.classList.add('error');
                    isValid = false;
                }
            }

            // Phone validation (10 digits)
            if (input.type === 'tel' && input.value.trim()) {
                const phoneRegex = /^[0-9]{10}$/;
                if (!phoneRegex.test(input.value.replace(/\D/g, ''))) {
                    input.classList.add('error');
                    isValid = false;
                }
            }
        });

        return isValid;
    };

    // ===========================================
    // MODAL FUNCTIONS
    // ===========================================

    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            body.style.overflow = 'hidden';
            // Focus first focusable element
            const focusable = modal.querySelector('button, [href], input, select, textarea');
            if (focusable) focusable.focus();
        }
    };

    window.hideModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            body.style.overflow = '';
        }
    };

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal') && event.target.classList.contains('show')) {
            event.target.classList.remove('show');
            body.style.overflow = '';
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(function(modal) {
                modal.classList.remove('show');
                body.style.overflow = '';
            });
        }
    });

    // ===========================================
    // LOGOUT MODAL FUNCTIONS
    // ===========================================

    window.showLogoutModal = function() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.add('show');
            body.style.overflow = 'hidden';
            // Focus on the "No" button for accessibility
            const noButton = modal.querySelector('.btn-secondary');
            if (noButton) noButton.focus();
        }
    };

    window.hideLogoutModal = function() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.remove('show');
            body.style.overflow = '';
        }
    };

    // ===========================================
    // TABLE SEARCH
    // ===========================================

    window.searchTable = function(inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);

        if (!input || !table) return;

        input.addEventListener('keyup', function() {
            const filter = this.value.toUpperCase();
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        });
    };

    // ===========================================
    // PRINT FUNCTIONALITY
    // ===========================================

    window.printContent = function(elementId) {
        const content = document.getElementById(elementId);
        if (!content) return;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin: 20px 0; }');
        printWindow.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
        printWindow.document.write('th { background-color: #f5f5f5; }');
        printWindow.document.write('.badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.print();
            printWindow.close();
        };
    };

    // ===========================================
    // EXPORT TABLE TO CSV
    // ===========================================

    window.exportTableToCSV = function(tableId, filename) {
        filename = filename || 'export.csv';
        const table = document.getElementById(tableId);
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(function(row) {
            const cols = row.querySelectorAll('td, th');
            let csvRow = [];

            cols.forEach(function(col) {
                // Skip action columns
                if (!col.classList.contains('action-buttons')) {
                    let text = col.innerText.replace(/"/g, '""');
                    csvRow.push('"' + text + '"');
                }
            });

            if (csvRow.length > 0) {
                csv.push(csvRow.join(','));
            }
        });

        downloadCSV(csv.join('\n'), filename);
    };

    function downloadCSV(csv, filename) {
        const csvFile = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    // ===========================================
    // DATE HELPERS
    // ===========================================

    window.setMinDate = function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
    };

    window.setMaxDate = function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('max', today);
        }
    };

    // ===========================================
    // MILK PRODUCTION CALCULATOR
    // ===========================================

    window.calculateTotalMilk = function() {
        const morning = parseFloat(document.getElementById('morning_milk')?.value) || 0;
        const afternoon = parseFloat(document.getElementById('afternoon_milk')?.value) || 0;
        const evening = parseFloat(document.getElementById('evening_milk')?.value) || 0;
        const total = morning + afternoon + evening;

        const totalInput = document.getElementById('total_milk');
        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }
    };

    // ===========================================
    // LOAN CALCULATOR
    // ===========================================

    window.calculateRemainingAmount = function() {
        const loanAmount = parseFloat(document.getElementById('loan_amount')?.value) || 0;
        const paidAmount = parseFloat(document.getElementById('paid_amount')?.value) || 0;
        const remaining = loanAmount - paidAmount;

        const remainingInput = document.getElementById('remaining_amount');
        if (remainingInput) {
            remainingInput.value = remaining.toFixed(2);
        }
    };

    // ===========================================
    // IMAGE PREVIEW
    // ===========================================

    window.previewImage = function(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview || !input.files || !input.files[0]) return;

        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (file.size > maxSize) {
            alert('File size must be less than 5MB');
            input.value = '';
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    };

    // ===========================================
    // FORM SUBMISSION WITH LOADING
    // ===========================================

    window.submitFormWithLoading = function(formId, buttonId) {
        const form = document.getElementById(formId);
        const button = document.getElementById(buttonId);

        if (!form || !button) return;

        form.addEventListener('submit', function(e) {
            // Basic validation
            if (!validateForm(formId)) {
                e.preventDefault();
                return;
            }

            button.disabled = true;
            button.classList.add('loading');
            const originalText = button.innerHTML;
            button.innerHTML = '<span>Processing...</span>';

            // Re-enable after timeout (fallback)
            setTimeout(function() {
                button.disabled = false;
                button.classList.remove('loading');
                button.innerHTML = originalText;
            }, 30000);
        });
    };

    // ===========================================
    // AUTO UPPERCASE
    // ===========================================

    window.autoUppercase = function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(start, end);
            });
        }
    };

    // ===========================================
    // RESPONSIVE TABLE ENHANCEMENTS
    // ===========================================

    function enhanceTablesForMobile() {
        if (window.innerWidth <= 767) {
            const tables = document.querySelectorAll('table');
            tables.forEach(function(table) {
                const headers = table.querySelectorAll('thead th');
                const headerLabels = [];

                headers.forEach(function(header) {
                    headerLabels.push(header.textContent.trim());
                });

                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const cells = row.querySelectorAll('td');
                    cells.forEach(function(cell, index) {
                        if (headerLabels[index]) {
                            cell.setAttribute('data-label', headerLabels[index]);
                        }
                    });
                });
            });
        }
    }

    // ===========================================
    // TOUCH SUPPORT
    // ===========================================

    function addTouchSupport() {
        // Add touch feedback to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(function(btn) {
            btn.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            }, { passive: true });

            btn.addEventListener('touchend', function() {
                this.style.transform = '';
            }, { passive: true });
        });
    }

    // ===========================================
    // SMOOTH SCROLL
    // ===========================================

    function smoothScrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    window.scrollToTop = smoothScrollToTop;

    // ===========================================
    // DEBOUNCE UTILITY
    // ===========================================

    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // ===========================================
    // INITIALIZE ON DOM READY
    // ===========================================

    document.addEventListener('DOMContentLoaded', function() {
        enhanceTablesForMobile();
        addTouchSupport();

        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(function(element) {
            element.setAttribute('title', element.getAttribute('data-tooltip'));
        });

        // Prevent double-click on buttons
        const forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function() {
                const submitBtns = this.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitBtns.forEach(function(btn) {
                    setTimeout(function() {
                        btn.disabled = true;
                    }, 10);
                });
            });
        });
    });

    // Re-enhance tables on resize
    window.addEventListener('resize', debounce(enhanceTablesForMobile, 250));

})();
