@php
    use Illuminate\Support\Str;

    $locale = $locale ?? app()->getLocale();
    $direction = $direction ?? (in_array($locale, ['ar', 'fa', 'ur']) ? 'rtl' : 'ltr');
    $isRtl = $direction === 'rtl';
    $fontFamily = $fontFamily ?? 'Inter';
    $logoUrl = $logoUrl ?? null;
    $moduleVersion = $moduleVersion ?? '1.0.0';
    // Clean font name for Google Fonts URL
    $googleFontFamily = str_replace(' ', '+', $fontFamily);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('applicationintegration-docs::doc.api_docs') }} | REST API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $googleFontFamily }}:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <style>
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --muted: #64748b;
            --text: #0f172a;
            --border: #e2e8f0;
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-sidebar: linear-gradient(180deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            --glass-bg: rgba(255, 255, 255, 0.8);
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --method-get: linear-gradient(135deg, #10b981, #059669);
            --method-post: linear-gradient(135deg, #3b82f6, #2563eb);
            --method-put: linear-gradient(135deg, #f59e0b, #d97706);
            --method-delete: linear-gradient(135deg, #ef4444, #dc2626);
        }
        body.dark {
            --bg: #0f172a;
            --surface: #1e293b;
            --surface-2: #334155;
            --muted: #94a3b8;
            --text: #f1f5f9;
            --border: #334155;
            --glass-bg: rgba(30, 41, 59, 0.9);
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: '{{ $fontFamily }}', 'Inter', system-ui, sans-serif; 
            background: var(--bg); 
            color: var(--text);
            line-height: 1.6;
        }
        
        a { color: inherit; text-decoration: none; }
        
        /* Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }
        
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 18px;
        }
        
        .header-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .version-badge {
            font-size: 11px;
            background: var(--accent);
            color: white;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
        }
        
        .search-container {
            flex: 1;
            max-width: 400px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 16px;
            padding-right: 60px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            color: var(--text);
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .search-kbd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            padding: 4px 8px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .control-btn, .control-select {
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .control-btn:hover, .control-select:hover {
            border-color: var(--accent);
            background: var(--surface-2);
        }
        
        /* Layout */
        .page {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
            max-width: 1600px;
            padding: 32px;
            margin: 0 auto;
        }
        
        /* Sidebar */
        .sidebar {
            position: sticky;
            top: 100px;
            align-self: start;
            background: var(--gradient-sidebar);
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--shadow-lg);
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }
        
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
        
        .sidebar-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .nav a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX({{ $isRtl ? '-4px' : '4px' }});
        }
        
        .nav a.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .nav-icon {
            width: 8px;
            height: 8px;
            background: currentColor;
            border-radius: 50%;
            opacity: 0.6;
        }
        
        /* Content */
        .content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-md);
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-desc {
            color: var(--muted);
            font-size: 15px;
            max-width: 700px;
            margin-top: 8px;
        }
        
        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--accent);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .section-badge:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        /* Quick Info Grid */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        
        .quick-item {
            padding: 16px;
            background: var(--surface-2);
            border-radius: 14px;
            border: 1px solid var(--border);
        }
        
        .quick-label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        
        .quick-value {
            font-size: 16px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        
        /* Endpoints */
        .endpoint {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            margin-top: 12px;
            background: var(--surface-2);
            transition: all 0.3s;
        }
        
        .endpoint:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent);
        }
        
        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        
        .method {
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.05em;
            color: white;
            font-family: 'JetBrains Mono', monospace;
        }
        
        .method-GET { background: var(--method-get); }
        .method-POST { background: var(--method-post); }
        .method-PUT { background: var(--method-put); }
        .method-DELETE { background: var(--method-delete); }
        
        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            color: var(--muted);
        }
        
        .auth-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent);
            border: 1px solid rgba(99, 102, 241, 0.2);
            font-weight: 600;
        }
        
        .endpoint-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .endpoint-summary {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .copy-btn:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        .copy-btn:active {
            transform: scale(0.95);
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 13px;
        }
        
        .table th {
            text-align: {{ $isRtl ? 'right' : 'left' }};
            padding: 10px 12px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 11px;
        }
        
        .table td {
            padding: 10px 12px;
            border: 1px solid var(--border);
            font-family: 'JetBrains Mono', monospace;
        }
        
        /* Callouts */
        .callout {
            padding: 14px 18px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            color: var(--text);
            margin: 12px 0;
            font-size: 13px;
        }
        
        .callout-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--accent);
        }
        
        .callout-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 6px;
        }
        
        .callout-item::before {
            content: '→';
            color: var(--accent);
            font-weight: 600;
        }
        
        /* Code Blocks */
        .code-shell {
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-top: 12px;
            background: #1e293b;
        }
        
        .code-tabs {
            display: flex;
            gap: 4px;
            padding: 12px;
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .code-tab {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: transparent;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .code-tab:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .code-tab.active {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.3);
        }
        
        .code-body {
            position: relative;
        }
        
        .code-copy {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .code-copy:hover {
            background: rgba(99, 102, 241, 0.3);
            color: white;
        }
        
        pre {
            margin: 0;
            padding: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            overflow-x: auto;
            color: #e2e8f0;
        }
        
        /* TOC Links */
        .toc-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .toc-link {
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface-2);
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .toc-link:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 1100px) {
            .page {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            .sidebar {
                position: relative;
                top: 0;
                max-height: none;
            }
            .header {
                padding: 12px 20px;
                flex-wrap: wrap;
            }
            .search-container {
                order: 3;
                max-width: 100%;
                margin-top: 12px;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--surface-2); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--muted); }
        
        /* Public Banner */
        .public-banner {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
            border: 1px dashed var(--accent);
        }
    </style>
</head>
<body class="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <header class="header">
        <div class="header-brand">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="logo-img" style="max-height: 45px; width: auto; border-radius: 8px; object-fit: contain;">
            @else
                <div class="logo-icon">API</div>
            @endif
            <div>
                <div class="header-title">{{ __('applicationintegration-docs::doc.api_docs') }}</div>
                <span class="version-badge">v{{ $moduleVersion }}</span>
            </div>
        </div>
        
        <div class="search-container">
            <input type="search" id="search-input" class="search-input" placeholder="{{ __('applicationintegration-docs::documentation.search_placeholder') ?? 'Search endpoints...' }}">
            <kbd class="search-kbd">Ctrl+K</kbd>
        </div>
        
        <div class="header-controls">

            <button id="theme-toggle" class="control-btn">
                <span id="theme-icon">🌙</span>
            </button>
        </div>
    </header>

    <div class="page">
        <aside class="sidebar">
            <div class="sidebar-title">{{ __('applicationintegration-docs::doc.toc') }}</div>
            <ul class="nav">
                @foreach($toc as $link)
                    <li>
                        <a href="#{{ $link['id'] }}">
                            <span class="nav-icon"></span>
                            {{ $link['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <main class="content">
            @if($isPublic)
                <div class="card public-banner">
                    <p>{{ __('applicationintegration-docs::doc.public_banner') }}</p>
                </div>
            @endif

            <div class="card">
                <div class="toc-links">
                    @foreach($toc as $link)
                        <a href="#{{ $link['id'] }}" class="toc-link">{{ $link['title'] }}</a>
                    @endforeach
                </div>
            </div>

            @foreach($sections as $section)
                <section id="{{ $section['id'] }}" class="card">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title">{{ $section['title'] }}</h3>
                            <p class="section-desc">{{ $section['description'] }}</p>
                        </div>
                        <button class="section-badge copy-anchor" data-copy="{{ url()->current() }}#{{ $section['id'] }}">
                            <span>#{{ $section['id'] }}</span>
                        </button>
                    </div>

                    @if(!empty($section['quick'] ?? []))
                        <div class="quick-grid">
                            @foreach($section['quick'] as $item)
                                <div class="quick-item">
                                    <div class="quick-label">{{ $item['label'] }}</div>
                                    <div class="quick-value">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($section['endpoints'] ?? []))
                        @foreach($section['endpoints'] as $endpoint)
                            <div class="endpoint" data-searchable>
                                <div class="endpoint-header">
                                    <span class="method method-{{ $endpoint['method'] }}">{{ $endpoint['method'] }}</span>
                                    <span class="endpoint-path">{{ $endpoint['path'] }}</span>
                                    @if(!empty($endpoint['auth']))
                                        <span class="auth-badge">🔒 {{ __('applicationintegration-docs::doc.auth_required') }}</span>
                                    @endif
                                </div>
                                
                                <h4 class="endpoint-title">{{ $endpoint['name'] }}</h4>
                                <p class="endpoint-summary">{{ $endpoint['summary'] }}</p>
                                
                                <button class="copy-btn copy-endpoint" data-copy="{{ $baseUrl }}{{ $endpoint['path'] }}">
                                    📋 {{ __('applicationintegration-docs::doc.copy_link') }}
                                </button>

                                @if(!empty($endpoint['headers'] ?? []))
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('applicationintegration-docs::doc.header') }}</th>
                                                <th>{{ __('applicationintegration-docs::doc.value') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($endpoint['headers'] as $header)
                                                <tr>
                                                    <td>{{ $header['name'] }}</td>
                                                    <td>{{ $header['value'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                @if(isset($endpoint['body']))
                                    <div class="callout">
                                        <div class="callout-title">📥 {{ __('applicationintegration-docs::doc.body_schema') }}</div>
                                    </div>
                                    <pre class="language-json"><code>{!! json_encode($endpoint['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</code></pre>
                                @endif

                                @if(!empty($endpoint['notes'] ?? []))
                                    <div class="callout">
                                        <div class="callout-title">💡 Notes</div>
                                        @foreach($endpoint['notes'] as $note)
                                            <div class="callout-item">{{ $note }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                @include('applicationintegration::documentation.partials.code', [
                                    'method' => $endpoint['method'],
                                    'path' => $endpoint['path'],
                                    'body' => $endpoint['body'] ?? null,
                                    'response' => $endpoint['response'] ?? [],
                                    'baseUrl' => $baseUrl
                                ])
                            </div>
                        @endforeach
                    @endif

                    @if(!empty($section['samples'] ?? []))
                        <div class="endpoint">
                            @include('applicationintegration::documentation.partials.code', [
                                'method' => $section['samples']['method'],
                                'path' => $section['samples']['path'],
                                'body' => $section['samples']['body'],
                                'response' => $section['samples']['response'],
                                'baseUrl' => $baseUrl
                            ])
                        </div>
                    @endif
                </section>
            @endforeach
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script>

        
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const savedTheme = localStorage.getItem('docs-theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark');
            themeIcon.textContent = '☀️';
        }
        
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            themeIcon.textContent = isDark ? '☀️' : '🌙';
            localStorage.setItem('docs-theme', isDark ? 'dark' : 'light');
        });
        
        // Search functionality
        const searchInput = document.getElementById('search-input');
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.endpoint').forEach(ep => {
                const text = ep.textContent.toLowerCase();
                ep.style.display = query === '' || text.includes(query) ? 'block' : 'none';
            });
        });
        
        // Keyboard shortcut (Ctrl+K)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
        
        // Code tabs
        document.querySelectorAll('.code-tabs').forEach((tabs) => {
            tabs.addEventListener('click', (e) => {
                if (!e.target.classList.contains('code-tab')) return;
                const lang = e.target.dataset.lang;
                const shell = tabs.closest('.code-shell');
                shell.querySelectorAll('.code-tab').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                shell.querySelectorAll('pre').forEach(pre => {
                    pre.style.display = pre.dataset.lang === lang ? 'block' : 'none';
                });
            });
        });
        
        // Copy buttons for data-copy attributes
        document.querySelectorAll('[data-copy]').forEach(el => {
            el.addEventListener('click', async () => {
                const val = el.getAttribute('data-copy');
                if (val && navigator.clipboard) {
                    await navigator.clipboard.writeText(val);
                    const originalText = el.innerHTML;
                    el.innerHTML = '✅ Copied!';
                    setTimeout(() => el.innerHTML = originalText, 1500);
                }
            });
        });
        
        // Copy buttons for code blocks
        document.querySelectorAll('.code-copy').forEach(btn => {
            btn.addEventListener('click', async () => {
                const codeBody = btn.closest('.code-body');
                if (codeBody) {
                    const visiblePre = codeBody.querySelector('pre:not([style*="none"])') || codeBody.querySelector('pre');
                    if (visiblePre && navigator.clipboard) {
                        await navigator.clipboard.writeText(visiblePre.textContent);
                        const originalText = btn.textContent;
                        btn.textContent = '✓ Copied!';
                        setTimeout(() => btn.textContent = originalText, 1500);
                    }
                }
            });
        });
        
        // Active section observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const id = entry.target.getAttribute('id');
                if (entry.isIntersecting) {
                    document.querySelectorAll('.nav a').forEach(link => link.classList.remove('active'));
                    const active = document.querySelector(`.nav a[href="#${id}"]`);
                    if (active) active.classList.add('active');
                }
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        
        document.querySelectorAll('section[id]').forEach(sec => observer.observe(sec));
        
        // Prism highlight
        Prism.highlightAll();
    </script>
</body>
</html>
