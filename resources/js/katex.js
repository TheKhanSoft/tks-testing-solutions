// Load KaTeX and make available globally
import katex from 'katex';
import renderMathInElement from 'katex/dist/contrib/auto-render';
import 'katex/dist/katex.min.css';

// Store KaTeX in window for global access
window.katex = katex;
window.renderMathInElement = renderMathInElement;

// Central KaTeX configuration
window.katexConfig = {
    delimiters: [
        { left: '$$', right: '$$', display: true },
        { left: '$', right: '$', display: false },
        { left: '\\(', right: '\\)', display: false },
        { left: '\\[', right: '\\]', display: true }
    ],
    custom: {
        families: ['KaTeX_AMS', 'KaTeX_Caligraphic:n4,n7', 'KaTeX_Fraktur:n4,n7',
          'KaTeX_Main:n4,n7,i4,i7', 'KaTeX_Math:i4,i7', 'KaTeX_Script',
          'KaTeX_SansSerif:n4,n7,i4', 'KaTeX_Size1', 'KaTeX_Size2', 'KaTeX_Size3',
          'KaTeX_Size4', 'KaTeX_Typewriter'],
    },
    throwOnError: false
};

// Function to initialize KaTeX rendering
function initializeKaTeX() {
    try {
        if (window.renderMathInElement && document.body) {
            window.renderMathInElement(document.body, window.katexConfig);
        }
    } catch (e) {
        console.error('KaTeX render error:', e);
    }
}

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Delay initialization slightly to ensure everything is loaded
    setTimeout(initializeKaTeX, 100);
});

// Re-render after Livewire updates
document.addEventListener('livewire:init', () => {
    Livewire.hook('commit', ({ component, succeed }) => {
        succeed(() => {
            // Give the DOM time to update before re-rendering
            setTimeout(initializeKaTeX, 100);
        });
    });
});

// Export a function that can be called to manually trigger rendering
window.renderMath = initializeKaTeX;

