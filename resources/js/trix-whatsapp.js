(function() {

    function handlePaste(event) {
        // We use the native paste event now, not Trix's custom event
        
        var clipboardData = event.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        var string = clipboardData.getData('text/plain');
        var html = clipboardData.getData('text/html');

        // Check if the pasted content looks like it needs formatting
        // We check for newlines, bullet points, or markdown markers
        if (string && (string.includes('\n') || string.includes('•') || string.match(/[*_~]/))) {
            
            // If it looks like WhatsApp content, we take over
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            // Normalize newlines
            string = string.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

            // Fix potential concatenation from bad copy-paste (e.g. "TerimakasihYth")
            // Insert newline if a lowercase letter is immediately followed by "Yth." or "Cc." or "1."
            string = string.replace(/([a-z])(Yth\.|Cc\.|1\.)/g, '$1\n$2');

            // Heuristic: Add newlines before specific patterns to improve layout
            // 1. Numbered lists (e.g. "1. Item", "2.Item") - checking for digit, dot, and then text
            string = string.replace(/(\s|^)(\d+\.[a-zA-Z])/g, '$1\n$2');
            
            // 2. Keywords like "Yth." or "Cc."
            string = string.replace(/(\s|^)(Yth\.|Cc\.)/gi, '$1\n$2');

            // 3. Bold headers (e.g. *SELAMAT PAGI*) - if preceded by space or start of line
            string = string.replace(/(\s|^)(\*[^*]+\*)/g, '$1\n$2');

            // Force newline before bullet points if they are inline
            string = string.replace(/([^\n])\s*•/g, '$1\n•');

            var formattedHtml = "";
            var lines = string.split('\n');
            var inList = false;

            lines.forEach(function(line, index) {
                var trimmedLine = line.trim();
                if (!trimmedLine) return; // Skip empty lines

                // Escape HTML tags
                var escapedLine = trimmedLine
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");

                // Formatting (Bold, Italic, Strike, Code)
                escapedLine = escapedLine.replace(/\*([^\s][^*]*[^\s]|[^\s])\*/g, "<strong>$1</strong>");
                escapedLine = escapedLine.replace(/_([^\s][^_]*[^\s]|[^\s])_/g, "<em>$1</em>");
                escapedLine = escapedLine.replace(/~([^\s][^~]*[^\s]|[^\s])~/g, "<del>$1</del>");
                escapedLine = escapedLine.replace(/```([^`]*)```/g, "<pre>$1</pre>");

                // Handle Bullet Points
                if (trimmedLine.startsWith('•')) {
                    if (!inList) {
                        formattedHtml += "<ul>";
                        inList = true;
                    }
                    var content = escapedLine.replace(/^•\s*/, '');
                    formattedHtml += "<li>" + content + "</li>";
                } else {
                    if (inList) {
                        formattedHtml += "</ul>";
                        inList = false;
                    }
                    
                    // Wrap non-list lines in p tags for proper block behavior in Trix
                    formattedHtml += "<p>" + escapedLine + "</p>";
                }
            });

            if (inList) {
                formattedHtml += "</ul>";
            }
            
            // Insert HTML immediately since we blocked the native event
            // We need to access the editor instance. The event target might be an input inside the editor.
            // We need to find the trix-editor element.
            var editorElement = event.target.closest('trix-editor');
            if (editorElement && editorElement.editor) {
                 editorElement.editor.insertHTML(formattedHtml);
            }
        }
    }

    function attachToElement(element) {
        if (element.dataset.whatsappPasteAttached) return;
        // Use Capture phase to intercept before Trix
        element.addEventListener("paste", handlePaste, true);
        element.dataset.whatsappPasteAttached = "true";
    }

    // Listen for new editors
    document.addEventListener("trix-initialize", function(event) {
        attachToElement(event.target);
    });

    // Attach to existing editors (in case script loads late)
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll("trix-editor").forEach(attachToElement);
    });
    
    // Immediate check in case DOM is already ready
    document.querySelectorAll("trix-editor").forEach(attachToElement);

})();
