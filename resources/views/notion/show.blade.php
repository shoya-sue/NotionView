<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - Notion Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .notion-block { margin-bottom: 0.5rem; }
        .notion-heading_1 { font-size: 2rem; font-weight: bold; margin-top: 2rem; margin-bottom: 1rem; }
        .notion-heading_2 { font-size: 1.5rem; font-weight: bold; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .notion-heading_3 { font-size: 1.25rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.5rem; }
        .notion-paragraph { line-height: 1.75; margin-bottom: 1rem; }
        .notion-bulleted_list_item { margin-left: 1.5rem; list-style-type: disc; }
        .notion-numbered_list_item { margin-left: 1.5rem; list-style-type: decimal; }
        .notion-code { background-color: #f3f4f6; padding: 1rem; border-radius: 0.375rem; font-family: monospace; overflow-x: auto; }
        .notion-quote { border-left: 3px solid #d1d5db; padding-left: 1rem; font-style: italic; color: #6b7280; }
        .notion-divider { border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col md:flex-row min-h-screen">
        {{-- ヘッダー（モバイル用） --}}
        <div class="md:hidden bg-gray-800 text-white p-4">
            <h1 class="text-xl font-bold">Laravel Notion Viewer</h1>
            <button id="menu-toggle" class="mt-2 p-2 bg-gray-700 rounded">
                メニュー
            </button>
        </div>
        
        {{-- サイドバー --}}
        <div id="sidebar" class="hidden md:block w-full md:w-64 bg-gray-800 text-white p-4 md:min-h-screen">
            <h1 class="text-xl font-bold mb-6 hidden md:block">Laravel Notion Viewer</h1>
            <a href="{{ route('home') }}" class="block mb-4 p-2 bg-gray-700 rounded hover:bg-gray-600 transition-colors text-center">
                ← ホームに戻る
            </a>
            <h2 class="text-lg font-semibold mb-4">ページ一覧</h2>
            <ul class="space-y-2">
                @foreach($pages as $sidebarPage)
                    <li>
                        <a href="{{ route('notion.show', $sidebarPage->id) }}" 
                           class="block p-3 rounded transition-colors 
                                  {{ $sidebarPage->id === $pageId ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            {{ $sidebarPage->title ?? 'Untitled' }}
                            @if($sidebarPage->description)
                                <span class="block text-sm text-gray-400 mt-1">{{ Str::limit($sidebarPage->description, 50) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
        {{-- メインコンテンツ --}}
        <div class="flex-1 p-4 md:p-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                    <h1 class="text-3xl font-bold mb-6">{{ $page->title ?? 'Untitled' }}</h1>
                    
                    @if($page->content)
                        <div class="prose max-w-none">
                            {!! nl2br(e($page->content)) !!}
                        </div>
                    @endif
                    
                    {{-- Notionブロックの表示 --}}
                    @if($blocks->count() > 0)
                        <div class="notion-content">
                            @foreach($blocks as $block)
                                @switch($block->type)
                                    @case('heading_1')
                                        <h1 class="notion-heading_1">{{ $block->text }}</h1>
                                        @break
                                    
                                    @case('heading_2')
                                        <h2 class="notion-heading_2">{{ $block->text }}</h2>
                                        @break
                                    
                                    @case('heading_3')
                                        <h3 class="notion-heading_3">{{ $block->text }}</h3>
                                        @break
                                    
                                    @case('paragraph')
                                        @if($block->text)
                                            <p class="notion-paragraph">{{ $block->text }}</p>
                                        @endif
                                        @break
                                    
                                    @case('bulleted_list_item')
                                        <ul>
                                            <li class="notion-bulleted_list_item">{{ $block->text }}</li>
                                        </ul>
                                        @break
                                    
                                    @case('numbered_list_item')
                                        <ol>
                                            <li class="notion-numbered_list_item">{{ $block->text }}</li>
                                        </ol>
                                        @break
                                    
                                    @case('to_do')
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" 
                                                   {{ isset($block->content['checked']) && $block->content['checked'] ? 'checked' : '' }}
                                                   disabled
                                                   class="mr-2">
                                            <span>{{ $block->text }}</span>
                                        </div>
                                        @break
                                    
                                    @case('toggle')
                                        <details class="mb-4">
                                            <summary class="cursor-pointer font-semibold">{{ $block->text }}</summary>
                                            <div class="ml-4 mt-2">
                                                {{-- 子要素があれば表示 --}}
                                            </div>
                                        </details>
                                        @break
                                    
                                    @case('code')
                                        <pre class="notion-code"><code>{{ $block->text }}</code></pre>
                                        @break
                                    
                                    @case('quote')
                                        <blockquote class="notion-quote">{{ $block->text }}</blockquote>
                                        @break
                                    
                                    @case('divider')
                                        <hr class="notion-divider">
                                        @break
                                    
                                    @case('image')
                                        @if(isset($block->content['external']['url']))
                                            <img src="{{ $block->content['external']['url'] }}" 
                                                 alt="Image" 
                                                 class="max-w-full h-auto rounded mb-4">
                                        @elseif(isset($block->content['file']['url']))
                                            <img src="{{ $block->content['file']['url'] }}" 
                                                 alt="Image" 
                                                 class="max-w-full h-auto rounded mb-4">
                                        @endif
                                        @break
                                    
                                    @default
                                        @if($block->text)
                                            <div class="notion-block">{{ $block->text }}</div>
                                        @endif
                                @endswitch
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
                            <p class="text-gray-700">
                                このページにはコンテンツがありません。
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // モバイルメニューのトグル
        document.getElementById('menu-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        });
    </script>
</body>
</html>