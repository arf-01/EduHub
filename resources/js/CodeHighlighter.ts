// Lightweight, Zero-Dependency Multi-Language VS Code Dark+ Syntax Highlighter & Structured Content Renderer

export interface CodeBlockData {
    type: 'text' | 'code';
    content: string;
    language?: string;
}

export function escapeHtml(str: string): string {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function unescapeHtml(str: string): string {
    if (!str) return '';
    return str
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&');
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

// Tokenize and highlight syntax using VS Code Dark+ Theme Colors
export function highlightCodeSyntax(code: string, language: string = 'cpp'): { html: string, lineCount: number } {
    if (!code) return { html: '', lineCount: 0 };

    let raw = unescapeHtml(code).trimEnd();
    const lang = (language || 'cpp').toLowerCase();

    const lines = raw.split('\n');

    // Language specific tokenizers
    let tokenRegex: RegExp;

    if (lang === 'python' || lang === 'py') {
        tokenRegex = /(#[^\n]*)|("""[\s\S]*?"""|'''[\s\S]*?'''|"[^"]*"|'[^']*')|(\b(?:def|class|if|elif|else|for|while|try|except|finally|with|as|import|from|return|yield|pass|break|continue|in|is|not|and|or|lambda|global|nonlocal|raise|assert|async|await)\b)|(\b(?:None|True|False|self|cls)\b)|(\b(?:int|float|str|list|dict|set|tuple|bool|bytes|type|len|range|enumerate|zip|map|filter|print|input|open|sum|min|max|abs|round|id|isinstance|issubclass)\b)|(\b\d+(\.\d+)?\b)|(\b[a-zA-Z_]\w*(?=\s*\())|(\b[a-zA-Z_]\w*\b)/g;
    } else if (lang === 'sql') {
        tokenRegex = /(--[^\n]*|\/\*[\s\S]*?\*\/)|("[^"]*"|'[^']*')|(\b(?:SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|JOIN|LEFT|RIGHT|INNER|OUTER|FULL|CROSS|ON|AND|OR|NOT|IN|BETWEEN|LIKE|IS|NULL|GROUP\s+BY|ORDER\s+BY|HAVING|LIMIT|OFFSET|AS|CREATE|TABLE|DATABASE|INDEX|VIEW|DROP|ALTER|ADD|SET|VALUES|DISTINCT|COUNT|SUM|AVG|MIN|MAX|PRIMARY\s+KEY|FOREIGN\s+KEY|REFERENCES|CASCADE|UNION|ALL|CASE|WHEN|THEN|ELSE|END|EXISTS|INTO)\b)/gi;
    } else if (lang === 'html' || lang === 'xml') {
        tokenRegex = /(<!--[\s\S]*?-->)|(<!DOCTYPE[^>]*>)|(<\/?)([a-zA-Z0-9-]+)([^>]*?)(\/?>)|("[^"]*"|'[^']*')/g;
    } else {
        // C, C++, Java, JS, TS, PHP, Go, Rust, C#, etc.
        tokenRegex = /(\/\/[^\n]*|\/\*[\s\S]*?\*\/|#[^\n]*)|("[^"]*"|'[^']*'|`[^`]*`)|(<[a-zA-Z0-9_\.]+\.?[a-zA-Z0-9]*>)|(\b(?:return|if|else|for|while|do|switch|case|break|continue|try|catch|throw|finally|import|from|export|default|function|class|extends|implements|interface|trait|package|new|typeof|instanceof|await|async|yield|debugger|static|public|private|protected|virtual|override|fn|let|mut|pub|match|select|defer|go|package|echo)\b)|(\b(?:int|float|double|char|bool|boolean|void|long|short|unsigned|signed|struct|union|enum|auto|const|typename|namespace|using|std|vector|string|list|map|set|cout|cin|endl|printf|scanf|nullptr|null|undefined|true|false|nil|var|let|const|val|byte|rune|uint|uint64|int64|usize|isize|str|String|Option|Result|Some|None|Ok|Err)\b)|(\b\d+(\.\d+)?\b)|(\b[a-zA-Z_]\w*(?=\s*\())|(\b[a-zA-Z_]\w*\b)/g;
    }

    const highlightedLines = lines.map(line => {
        let result = '';
        let lastIdx = 0;
        let match: RegExpExecArray | null;

        tokenRegex.lastIndex = 0;

        while ((match = tokenRegex.exec(line)) !== null) {
            if (match.index > lastIdx) {
                result += escapeHtml(line.substring(lastIdx, match.index));
            }

            const [fullMatch] = match;

            if (lang === 'sql') {
                const [, comment, str, keyword] = match;
                if (comment) {
                    result += `<span class="text-emerald-500 italic">${escapeHtml(comment)}</span>`;
                } else if (str) {
                    result += `<span class="text-amber-300">${escapeHtml(str)}</span>`;
                } else if (keyword) {
                    result += `<span class="text-purple-400 font-bold uppercase">${escapeHtml(keyword)}</span>`;
                } else {
                    result += escapeHtml(fullMatch);
                }
            } else if (lang === 'html' || lang === 'xml') {
                const [, comment, doctype, tagOpen, tagName, attrs, tagClose, str] = match;
                if (comment) {
                    result += `<span class="text-emerald-500 italic">${escapeHtml(comment)}</span>`;
                } else if (doctype) {
                    result += `<span class="text-slate-400 font-bold">${escapeHtml(doctype)}</span>`;
                } else if (tagName) {
                    let formattedAttrs = escapeHtml(attrs || '').replace(/([a-zA-Z0-9_:-]+)(=)("[^"]*"|'[^']*')/g, '<span class="text-cyan-300">$1</span>$2<span class="text-amber-300">$3</span>');
                    result += `<span class="text-slate-500">${escapeHtml(tagOpen)}</span><span class="text-blue-400 font-semibold">${escapeHtml(tagName)}</span>${formattedAttrs}<span class="text-slate-500">${escapeHtml(tagClose)}</span>`;
                } else if (str) {
                    result += `<span class="text-amber-300">${escapeHtml(str)}</span>`;
                } else {
                    result += escapeHtml(fullMatch);
                }
            } else {
                const [, comment, str, header, controlFlow, typeKeyword, num, fn, ident] = match;

                if (comment) {
                    if (comment.startsWith('#include') || comment.startsWith('#define')) {
                        const directiveEscaped = comment.replace(/(#include|#define)/, '<span class="text-purple-400 font-bold">$1</span>');
                        result += directiveEscaped.replace(/(<[a-zA-Z0-9_\.]+\.?[a-zA-Z0-9]*>)/, (h) => `<span class="text-orange-300 font-medium">${escapeHtml(h)}</span>`);
                    } else {
                        result += `<span class="text-emerald-500 italic">${escapeHtml(comment)}</span>`;
                    }
                } else if (str) {
                    result += `<span class="text-amber-300">${escapeHtml(str)}</span>`;
                } else if (header) {
                    result += `<span class="text-orange-300 font-medium">${escapeHtml(header)}</span>`;
                } else if (controlFlow) {
                    result += `<span class="text-purple-400 font-bold">${escapeHtml(controlFlow)}</span>`;
                } else if (typeKeyword) {
                    result += `<span class="text-blue-400 font-semibold">${escapeHtml(typeKeyword)}</span>`;
                } else if (num) {
                    result += `<span class="text-emerald-300 font-mono">${escapeHtml(num)}</span>`;
                } else if (fn) {
                    result += `<span class="text-yellow-300 font-medium">${escapeHtml(fn)}</span>`;
                } else if (ident) {
                    if (['std', 'cout', 'cin', 'endl', 'console', 'log', 'document', 'window'].includes(ident)) {
                        result += `<span class="text-cyan-300 font-medium">${escapeHtml(ident)}</span>`;
                    } else {
                        result += `<span class="text-sky-200">${escapeHtml(ident)}</span>`;
                    }
                } else {
                    result += escapeHtml(fullMatch);
                }
            }

            lastIdx = tokenRegex.lastIndex;
        }

        if (lastIdx < line.length) {
            result += escapeHtml(line.substring(lastIdx));
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

    let raw = unescapeHtml(text);
    const parts = raw.split(/(`[^`]+`)/g);
    return parts.map(part => {
        if (part.startsWith('`') && part.endsWith('`') && part.length >= 2) {
            const inner = part.slice(1, -1);
            return `<code class="bg-slate-800 text-amber-300 px-1.5 py-0.5 rounded font-mono text-xs sm:text-sm font-semibold border border-slate-700 mx-0.5">${escapeHtml(inner)}</code>`;
        }
        return escapeHtml(part);
    }).join('');
}

// Render a complete VS Code style code box
export function renderCodeBoxHtml(code: string, language: string = 'cpp'): string {
    const highlighted = highlightCodeSyntax(code, language);
    const langBadge = (language || 'code').toUpperCase();

    let linesGutter = '';
    for (let i = 1; i <= highlighted.lineCount; i++) {
        linesGutter += `<span>${i}</span>`;
    }

    return `
        <div class="rounded-xl overflow-hidden shadow-md border border-slate-700/80 bg-[#0d1117] font-mono text-sm my-3 text-left">
            <div class="bg-[#161b22] border-b border-slate-700/80 px-3.5 py-2 flex justify-between items-center text-xs select-none">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
                    </div>
                    <span class="ml-2 font-bold text-indigo-400 tracking-wider text-[10px] bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-700/40">${langBadge}</span>
                </div>
            </div>
            <div class="p-3.5 overflow-x-auto flex text-slate-200 text-xs sm:text-sm leading-relaxed">
                <div class="select-none text-slate-600 text-right pr-3 border-r border-slate-800 font-mono flex flex-col">${linesGutter}</div>
                <pre class="pl-3 font-mono overflow-x-auto text-slate-200 whitespace-pre"><code>${highlighted.html}</code></pre>
            </div>
        </div>
    `;
}

// Global browser attachment for Blade templates
if (typeof window !== 'undefined') {
    (window as any).QuizHighlighter = {
        parseContentWithCode,
        highlightCodeSyntax,
        formatInlineCode,
        renderCodeBoxHtml,
        escapeHtml,
        unescapeHtml
    };
}
