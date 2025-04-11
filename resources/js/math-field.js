import { MathfieldElement } from 'mathlive';

// Register the custom element if not already registered
if (!customElements.get('math-field')) {
    customElements.define('math-field', MathfieldElement);
}

// Initialize MathField with proper configuration
function initializeMathField(element) {
    if (!(element instanceof MathfieldElement)) {
        return;
    }

    // Configure the MathField instance using properties
    element.virtualKeyboardMode = 'manual';
    element.virtualKeyboards = 'all';
    element.smartMode = true;
    element.smartFence = true;
    element.selectOnFocus = false;
    element.readOnly = false;

    // Handle focus events
    element.addEventListener('focus', () => {
        element.readOnly = false;
    });

    // Handle blur events
    element.addEventListener('blur', () => {
        // Small delay to ensure content is saved before potentially becoming read-only
        setTimeout(() => {
            element.readOnly = false;
        }, 100);
    });

    // Ensure the field remains editable after any mathfield-rendered event
    element.addEventListener('mathfield-rendered', () => {
        element.readOnly = false;
    });
}

// Function to initialize all math fields on the page
function initializeAllMathFields() {
    document.querySelectorAll('math-field').forEach(initializeMathField);
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeAllMathFields);

// Initialize new math fields that might be added dynamically
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node instanceof Element) {
                if (node.tagName.toLowerCase() === 'math-field') {
                    initializeMathField(node);
                }
                node.querySelectorAll('math-field').forEach(initializeMathField);
            }
        });
    });
});

// Start observing the document for dynamically added math fields
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Make available globally
window.MathfieldElement = MathfieldElement;
window.initializeMathField = initializeMathField;
window.initializeAllMathFields = initializeAllMathFields;
