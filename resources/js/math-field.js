import { MathfieldElement } from 'mathlive';

// Optional: Customize configurations (fonts directory, keybindings, etc.)
// MathfieldElement.fontsDirectory = '/assets/mathlive/fonts'; // Example if self-hosting fonts
// MathfieldElement.readOnly = true; // Set default global options if needed

// Register the component (usually not strictly needed if using it directly via new MathfieldElement())
// customElements.define('math-field', MathfieldElement); // It often registers itself

// Make it available globally if needed, or use within specific components/modules
window.MathfieldElement = MathfieldElement;