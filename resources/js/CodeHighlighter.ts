// Lightweight, Zero-Dependency VS Code Dark+ Syntax Highlighter & Markdown Code Renderer

export interface CodeBlockData {
    type: 'text' | 'code';
    content: string;
    language?: string;
}

export function parseContentWithCode(rawText: string): CodeBlockData[] {
    if (!rawText) return [];

    const blocks: CodeBlockData[] = [];
    const codeBlockRegex = /```([a-zA-Z0-9_+#-]*)\r?\n([\s\S]*?)```/g;

    let lastIndex = 0;
    let match: RegExpExecArray | null;

    while ((match = codeBlockRegex.exec(rawText)) !== null) {
        if (match.index > lastIndex) {
            blocks.push({
                type: 'text',
                content: rawText.substring(lastIndex, match.index)
            });
        }

        const lang = match[1].trim().toLowerCase() || 'code';
        const code = match[2];

        blocks.push({
            type: 'code',
            language: lang,
            content: code
        });

        lastIndex = codeBlockRegex.lastIndex;
    }

    if (lastIndex < rawText.length) {
        blocks.push({
            type: 'text',
            content: rawText.substring(lastIndex)
        });
    }

    return blocks;
}

// Tokenize and highlight syntax using exact VS Code Dark+ Theme Colors
export function highlightCodeSyntax(code: string, language: string = 'cpp'): { html: string, lineCount: number } {
    if (!code) return { html: '', lineCount: 0 };

    // 1. Normalize code by unescaping any pre-escaped HTML entities (e.g. &lt; -> <, &gt; -> >)
    let raw = code
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&')
        .trimEnd();

    // 2. Remove default placeholder comment if left by teacher
    raw = raw.replace(/\/\/\s*Paste your raw code snippet here directly from VS Code \/ Code::Blocks\r?\n?/gi, '').trim();

    const escape = (str: string) => str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const lines = raw.split('\n');

    const tokenRegex = /(\/\/[^\n]*|\/\*[\s\S]*?\*\/|#[^\n]*)|("[^"]*"|'[^']*')|(<[a-zA-Z0-9_\.]+\.?[a-zA-Z0-9]*>)|(\b(?:return|if|else|for|while|do|switch|case|break|continue|try|catch|throw|import|from|export|default)\b)|(\b(?:int|float|double|char|bool|boolean|void|long|short|unsigned|signed|struct|class|union|enum|auto|const|static|public|private|protected|virtual|override|typename|namespace|using|std|vector|string|list|map|set|cout|cin|endl|printf|scanf)\b)|(\b\d+(\.\d+)?\b)|(\b[a-zA-Z_]\w*(?=\s*\())|(\b[a-zA-Z_]\w*\b)/g;

    const highlightedLines = lines.map(line => {
        let result = '';
        let lastIdx = 0;
        let match: RegExpExecArray | null;

        tokenRegex.lastIndex = 0;

        while ((match = tokenRegex.exec(line)) !== null) {
            // Append and escape unhighlighted text before this match (operators like <<, >>, +, -, =, etc)
            if (match.index > lastIdx) {
                result += escape(line.substring(lastIdx, match.index));
            }

            const [fullMatch, comment, str, header, controlFlow, typeKeyword, num, numDec, fn, ident] = match;

            if (comment) {
                if (comment.startsWith('#include') || comment.startsWith('#define')) {
                    const directiveEscaped = comment.replace(/(#include|#define)/, '<span class="text-purple-400 font-bold">$1</span>');
                    result += directiveEscaped.replace(/(<[a-zA-Z0-9_\.]+\.?[a-zA-Z0-9]*>)/, (h) => `<span class="text-orange-300 font-medium">${escape(h)}</span>`);
                } else {
                    result += `<span class="text-emerald-500 italic">${escape(comment)}</span>`;
                }
            } else if (str) {
                result += `<span class="text-amber-300">${escape(str)}</span>`;
            } else if (header) {
                result += `<span class="text-orange-300 font-medium">${escape(header)}</span>`;
            } else if (controlFlow) {
                result += `<span class="text-purple-400 font-bold">${escape(controlFlow)}</span>`;
            } else if (typeKeyword) {
                result += `<span class="text-blue-400 font-semibold">${escape(typeKeyword)}</span>`;
            } else if (num) {
                result += `<span class="text-emerald-300 font-mono">${escape(num)}</span>`;
            } else if (fn) {
                result += `<span class="text-yellow-300 font-medium">${escape(fn)}</span>`;
            } else if (ident) {
                if (ident === 'std' || ident === 'cout' || ident === 'cin' || ident === 'endl') {
                    result += `<span class="text-cyan-300 font-medium">${escape(ident)}</span>`;
                } else {
                    result += `<span class="text-sky-200">${escape(ident)}</span>`;
                }
            } else {
                result += escape(fullMatch);
            }

            lastIdx = tokenRegex.lastIndex;
        }

        if (lastIdx < line.length) {
            result += escape(line.substring(lastIdx));
        }

        return result;
    });

    return {
        html: highlightedLines.join('\n'),
        lineCount: lines.length
    };
}

// Inline code helper: replaces `code` with high-contrast styled span badge
export function formatInlineCode(text: string): string {
    if (!text) return '';

    let raw = text
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&');

    const escape = (str: string) => str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const parts = raw.split(/(`[^`]+`)/g);
    return parts.map(part => {
        if (part.startsWith('`') && part.endsWith('`') && part.length >= 2) {
            const inner = part.slice(1, -1);
            return `<code class="bg-slate-800 text-amber-300 px-2 py-0.5 rounded font-mono text-sm font-bold border border-slate-700 shadow-sm mx-0.5">${escape(inner)}</code>`;
        }
        return escape(part);
    }).join('');
}
