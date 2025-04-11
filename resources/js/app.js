// resources/js/app.js

// Import Bootstrap or other framework JS if you use them
// import './bootstrap';

// First import KaTeX for math rendering in static content
import './katex'; // Keep for displaying math outside editor

// Import the editor factory which uses the custom build
import './math-field';

// Initialize math rendering after everything is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize math rendering
    if (window.renderMath) {
        window.renderMath();
    }
    
    // Initialize math fields
    if (window.initializeAllMathFields) {
        window.initializeAllMathFields();
    }
});

// Re-initialize math fields after Livewire updates
document.addEventListener('livewire:navigated', () => {
    if (window.initializeAllMathFields) {
        window.initializeAllMathFields();
    }
});
function setupPrintHandler() {
    // Store original print function
    const originalPrint = window.print;
    
    // Override print function
    window.print = function() {
        // Call original print
        originalPrint.apply(window);
        
        // Schedule Livewire restart after print dialog closes
        setTimeout(() => {
            if (window.Livewire) {
                window.Livewire.restart();
            }
        }, 500);
    };
    
    // Add listener for the afterprint event as backup
    window.addEventListener('afterprint', () => {
        if (window.Livewire) {
            setTimeout(() => {
                window.Livewire.restart();
            }, 300);
        }
    });
}
