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

    // Core configuration
    const config = {
        defaultMode: 'math',
        inlineShortcuts: {
            ...MathfieldElement.defaultInlineShortcuts,
        },
        keybindings: {
            ...MathfieldElement.defaultKeybindings,
        },
        smartMode: true,
        smartFence: true,
        removeExtraneousParentheses: true,
        letterShapeStyle: 'tex',
        iron: false, // Allows editing
        virtualKeyboardMode: 'manual',
        virtualKeyboards: 'all',
        customVirtualKeyboardLayers: {},
        customVirtualKeyboards: {},
        mathVirtualKeyboard: true,
        mathModeSpace: '0',
    };

    // Apply core configuration
    Object.entries(config).forEach(([key, value]) => {
        try {
            element[key] = value;
        } catch (e) {
            console.warn(`Failed to set ${key}:`, e);
        }
    });

    // Additional settings
    element.style.minHeight = '50px';
    element.style.maxHeight = '200px';
    element.style.overflowY = 'auto';

    // Event handlers
    const handlers = {
        focus: () => {
            element.executeCommand(['switchMode', 'math']);
            element.select();
        },
        blur: () => {
            if (element.isConnected) {
                element.executeCommand(['complete']);
            }
        },
        change: () => {
            if (element.isConnected) {
                element.executeCommand(['complete']);
            }
        },
        'math-error': (err) => {
            console.warn('Math error:', err);
        }
    };

    // Attach event handlers
    Object.entries(handlers).forEach(([event, handler]) => {
        element.addEventListener(event, handler);
    });

    // Initial focus
    setTimeout(() => {
        element.executeCommand(['switchMode', 'math']);
        try {
            element.focus();
        } catch (e) {
            console.warn('Could not focus math field:', e);
        }
    }, 100);
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
