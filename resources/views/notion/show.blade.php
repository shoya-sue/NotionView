<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - ゲームまとめ</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Noto+Sans+JP:wght@400;700&display=swap');
        .pixel-font { font-family: 'Press Start 2P', cursive; }
        body { font-family: 'Noto Sans JP', sans-serif; }
        .gradient-gaming { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-retro { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px) scale(1.02); }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        .float-animation { animation: float 3s ease-in-out infinite; }
        
        /* ゲーム風のコンテンツスタイル */
        .notion-block { margin-bottom: 1rem; }
        .notion-heading_1 { 
            font-size: 2rem; 
            font-weight: bold; 
            margin-top: 2rem; 
            margin-bottom: 1rem; 
            color: #e9d5ff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .notion-heading_2 { 
            font-size: 1.5rem; 
            font-weight: bold; 
            margin-top: 1.5rem; 
            margin-bottom: 0.75rem; 
            color: #ddd6fe;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        .notion-heading_3 { 
            font-size: 1.25rem; 
            font-weight: 600; 
            margin-top: 1rem; 
            margin-bottom: 0.5rem; 
            color: #c4b5fd;
        }
        .notion-paragraph { line-height: 1.75; margin-bottom: 1rem; color: #e5e7eb; }
        .notion-bulleted_list_item { margin-left: 1.5rem; list-style-type: disc; color: #e5e7eb; }
        .notion-numbered_list_item { margin-left: 1.5rem; list-style-type: decimal; color: #e5e7eb; }
        .notion-code { 
            background-color: rgba(0,0,0,0.5); 
            padding: 1rem; 
            border-radius: 0.5rem; 
            font-family: 'Courier New', monospace; 
            overflow-x: auto;
            color: #a78bfa;
            border: 1px solid rgba(147, 51, 234, 0.3);
        }
        .notion-quote { 
            border-left: 4px solid #a78bfa; 
            padding-left: 1rem; 
            font-style: italic; 
            color: #c4b5fd;
            background-color: rgba(147, 51, 234, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .notion-divider { border-top: 2px solid rgba(147, 51, 234, 0.3); margin: 2rem 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="flex flex-col md:flex-row min-h-screen">
        {{-- ヘッダー（モバイル用） --}}
        <div class="md:hidden gradient-gaming text-white p-4 shadow-xl">
            <h1 class="text-xl font-bold flex items-center">
                <i class="fas fa-gamepad mr-2"></i>
                <span class="pixel-font text-sm">ゲームまとめ</span>
            </h1>
            <button id="menu-toggle" class="mt-2 p-2 bg-white/20 backdrop-blur rounded-lg">
                <i class="fas fa-bars"></i> メニュー
            </button>
        </div>
        
        {{-- サイドバー --}}
        <div id="sidebar" class="hidden md:block w-full md:w-72 bg-black/40 backdrop-blur-lg text-white p-4 md:min-h-screen overflow-y-auto border-r border-purple-500/30">
            <h1 class="text-xl font-bold mb-4 hidden md:flex items-center">
                <i class="fas fa-gamepad text-purple-400 mr-3 text-3xl float-animation"></i>
                <div>
                    <span class="pixel-font text-sm block text-purple-300">ゲームまとめ</span>
                    <span class="text-xs text-gray-400">Game Collection</span>
                </div>
            </h1>
            
            <a href="{{ route('home') }}" class="block mb-4 p-3 bg-gradient-to-r from-purple-600/30 to-pink-600/30 rounded-lg hover:from-purple-600/50 hover:to-pink-600/50 transition-all text-center border border-purple-500/30">
                <i class="fas fa-home mr-2"></i> ホームに戻る
            </a>
            
            {{-- カテゴリーフィルター --}}
            @php
                $categories = $pages->flatMap(function($page) {
                    return is_array($page->category) ? $page->category : [$page->category];
                })->filter()->unique()->sort();
            @endphp
            @if($categories->count() > 0)
                <div class="mb-4 bg-white/10 rounded-lg p-3 backdrop-blur">
                    <h3 class="text-sm font-bold mb-3 text-purple-300 flex items-center">
                        <i class="fas fa-folder-open mr-2"></i>プラットフォーム
                    </h3>
                    <div class="space-y-1">
                        <button onclick="filterByCategory('')" class="category-filter w-full text-left px-3 py-2 rounded-lg hover:bg-purple-600/30 text-sm transition-all flex items-center">
                            <i class="fas fa-infinity mr-2 text-xs"></i> すべて
                        </button>
                        @foreach($categories as $category)
                            <button onclick="filterByCategory('{{ $category }}')" class="category-filter w-full text-left px-3 py-2 rounded-lg hover:bg-purple-600/30 text-sm transition-all flex items-center">
                                @php
                                    $icons = [
                                        'FAMICON' => 'fa-ghost',
                                        'SUPER FAMICON' => 'fa-star',
                                        'Nintendo64' => 'fa-cube',
                                        'PlayStation' => 'fa-play',
                                        'PC' => 'fa-desktop',
                                        'GAMEBOY' => 'fa-mobile',
                                    ];
                                    $icon = $icons[$category] ?? 'fa-gamepad';
                                @endphp
                                <i class="fas {{ $icon }} mr-2 text-xs"></i> {{ $category }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <h2 class="text-lg font-bold mb-4 text-purple-300 flex items-center">
                <i class="fas fa-book mr-2"></i>ゲームコレクション
            </h2>
            <ul class="space-y-2">
                @foreach($pages as $sidebarPage)
                    <li class="page-item card-hover" data-category="{{ is_array($sidebarPage->category) ? implode(',', $sidebarPage->category) : ($sidebarPage->category ?? '') }}">
                        <a href="{{ route('notion.show', $sidebarPage->id) }}" 
                           class="block p-3 rounded-lg transition-all backdrop-blur border {{ $sidebarPage->id === $pageId ? 'bg-purple-600/30 border-purple-400' : 'hover:bg-white/10 bg-white/5 border-purple-500/20' }}">
                            <div class="font-bold text-white">{{ $sidebarPage->title ?? 'Untitled' }}</div>
                            @if($sidebarPage->category)
                                @if(is_array($sidebarPage->category))
                                    @foreach($sidebarPage->category as $cat)
                                        <span class="inline-block px-2 py-1 text-xs gradient-gaming text-white rounded-full mt-1 mr-1">
                                            {{ $cat }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="inline-block px-2 py-1 text-xs gradient-gaming text-white rounded-full mt-1">
                                        {{ $sidebarPage->category }}
                                    </span>
                                @endif
                            @endif
                            @if($sidebarPage->description)
                                <span class="block text-sm text-gray-300 mt-1">{{ Str::limit($sidebarPage->description, 50) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
        {{-- メインコンテンツ --}}
        <div class="flex-1 p-2 md:p-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-black/40 backdrop-blur-lg rounded-xl shadow-2xl p-4 md:p-6 border border-purple-500/30">
                    {{-- ページヘッダー --}}
                    <div class="mb-4">
                        <h1 class="pixel-font text-xl md:text-2xl mb-3 text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400">
                            {{ $page->title ?? 'Untitled' }}
                        </h1>
                        
                        {{-- ページメタデータ --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if(isset($page->category) && $page->category)
                                @if(is_array($page->category))
                                    @foreach($page->category as $cat)
                                        <span class="inline-block px-3 py-1 text-sm gradient-gaming text-white rounded-full">
                                            <i class="fas fa-gamepad mr-1 text-xs"></i> {{ $cat }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="inline-block px-3 py-1 text-sm gradient-gaming text-white rounded-full">
                                        <i class="fas fa-gamepad mr-1 text-xs"></i> {{ $page->category }}
                                    </span>
                                @endif
                            @endif
                            @if(isset($page->status) && $page->status)
                                <span class="inline-block px-3 py-1 text-sm bg-green-500/50 text-green-200 rounded-full">
                                    <i class="fas fa-check-circle mr-1 text-xs"></i> {{ $page->status }}
                                </span>
                            @endif
                            @if(isset($page->tags) && count($page->tags) > 0)
                                @foreach($page->tags as $tag)
                                    <span class="inline-block px-3 py-1 text-sm bg-purple-600/30 text-purple-200 rounded-full">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                    {{-- コンテンツエリア --}}
                    <div class="bg-white/5 rounded-lg p-4 backdrop-blur">
                        @if($page->content)
                            <div class="prose max-w-none text-gray-200">
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
                                                       class="mr-2 accent-purple-500">
                                                <span class="text-gray-200">{{ $block->text }}</span>
                                            </div>
                                            @break
                                        
                                        @case('toggle')
                                            <details class="mb-4 bg-purple-600/10 rounded-lg p-3">
                                                <summary class="cursor-pointer font-semibold text-purple-300">{{ $block->text }}</summary>
                                                <div class="ml-4 mt-2 text-gray-300">
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
                                                     class="max-w-full h-auto rounded-lg mb-4 shadow-lg">
                                            @elseif(isset($block->content['file']['url']))
                                                <img src="{{ $block->content['file']['url'] }}" 
                                                     alt="Image" 
                                                     class="max-w-full h-auto rounded-lg mb-4 shadow-lg">
                                            @endif
                                            @break
                                        
                                        @default
                                            @if($block->text)
                                                <div class="notion-block text-gray-200">{{ $block->text }}</div>
                                            @endif
                                    @endswitch
                                @endforeach
                            </div>
                        @else
                            <div class="bg-purple-600/20 border-l-4 border-purple-400 p-4 rounded">
                                <p class="text-purple-200">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    このページにはコンテンツがありません。
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    {{-- フッター --}}
                    <div class="mt-4 pt-3 border-t border-purple-500/30 flex justify-between items-center">
                        <a href="{{ route('home') }}" class="text-purple-400 hover:text-purple-300 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>ホームに戻る
                        </a>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-code mr-1"></i> 
                            <a href="https://github.com/shoya-sue" target="_blank" class="text-purple-400 hover:text-purple-300 transition-colors">
                                shoya-sue
                            </a>
                        </div>
                    </div>
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
        
        // カテゴリーフィルター機能
        function filterByCategory(category) {
            const pageItems = document.querySelectorAll('.page-item');
            const categoryButtons = document.querySelectorAll('.category-filter');
            
            // ボタンのアクティブ状態を更新
            categoryButtons.forEach(btn => {
                btn.classList.remove('bg-purple-600/30');
                if (category === '' && btn.textContent.trim().includes('すべて')) {
                    btn.classList.add('bg-purple-600/30');
                } else if (btn.textContent.trim().includes(category) && category !== '') {
                    btn.classList.add('bg-purple-600/30');
                }
            });
            
            // ページアイテムの表示/非表示を切り替え
            pageItems.forEach(item => {
                const itemCategories = item.dataset.category ? item.dataset.category.split(',') : [];
                if (category === '' || itemCategories.includes(category)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>