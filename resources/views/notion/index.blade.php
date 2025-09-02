<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ゲームまとめ</title>
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
        @keyframes glow { 0%, 100% { box-shadow: 0 0 20px rgba(147, 51, 234, 0.6); } 50% { box-shadow: 0 0 30px rgba(147, 51, 234, 0.8); } }
        .glow-effect { animation: glow 2s ease-in-out infinite; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
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
                @foreach($pages as $page)
                    <li class="page-item card-hover" data-category="{{ is_array($page->category) ? implode(',', $page->category) : ($page->category ?? '') }}">
                        <a href="{{ route('notion.show', $page->id) }}" 
                           class="block p-3 rounded-lg hover:bg-white/10 transition-all bg-white/5 backdrop-blur border border-purple-500/20">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="font-bold text-white">{{ $page->title ?? 'Untitled' }}</div>
                                    @if($page->category)
                                        @if(is_array($page->category))
                                            @foreach($page->category as $cat)
                                                <span class="inline-block px-2 py-1 text-xs gradient-gaming text-white rounded-full mt-1 mr-1">
                                                    {{ $cat }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-block px-2 py-1 text-xs gradient-gaming text-white rounded-full mt-1">
                                                {{ $page->category }}
                                            </span>
                                        @endif
                                    @endif
                                    @if($page->status)
                                        <span class="inline-block px-2 py-1 text-xs bg-green-500/50 text-green-200 rounded-full mt-1 ml-1">
                                            {{ $page->status }}
                                        </span>
                                    @endif
                                    @if($page->description)
                                        <span class="block text-sm text-gray-300 mt-1">{{ Str::limit($page->description, 50) }}</span>
                                    @endif
                                    @if(isset($page->tags) && count($page->tags) > 0)
                                        <div class="mt-1">
                                            @foreach($page->tags as $tag)
                                                <span class="inline-block px-2 py-0.5 text-xs bg-purple-600/30 text-purple-200 rounded mr-1">
                                                    #{{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
        {{-- メインコンテンツ --}}
        <div class="flex-1 p-2 md:p-4">
            <div class="max-w-6xl mx-auto">
                {{-- ヒーローセクション --}}
                <div class="bg-black/40 backdrop-blur-lg rounded-xl shadow-2xl p-4 md:p-6 mb-4 border border-purple-500/30 glow-effect">
                    <div class="text-center mb-4">
                        <h2 class="pixel-font text-xl md:text-3xl mb-3 text-transparent bg-clip-text gradient-retro bg-gradient-to-r from-pink-400 to-purple-400">
                            GAME COLLECTION HUB
                        </h2>
                        <p class="text-gray-300 text-sm md:text-base mb-4">
                            あらゆるゲーム情報をまとめています
                        </p>
                        
                        {{-- 統計情報 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div class="bg-white/10 rounded-lg p-3 backdrop-blur">
                                <i class="fas fa-folder text-2xl text-pink-400 mb-1"></i>
                                <div class="text-xl font-bold text-white">{{ $categories->count() }}</div>
                                <div class="text-xs text-gray-400">プラットフォーム</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-3 backdrop-blur">
                                <i class="fas fa-sync text-2xl text-blue-400 mb-1"></i>
                                <div class="text-xl font-bold text-white">リアルタイム</div>
                                <div class="text-xs text-gray-400">Notion連携</div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- フッター情報 --}}
                    <div class="border-t border-purple-500/30 pt-3 flex flex-col md:flex-row justify-between items-center">
                        <div class="text-sm text-gray-400 mb-2 md:mb-0">
                            <i class="fas fa-code mr-1"></i> Developed by 
                            <a href="https://github.com/shoya-sue" target="_blank" class="text-purple-400 hover:text-purple-300 transition-colors font-bold">
                                <i class="fab fa-github mr-1"></i>shoya-sue
                            </a>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="https://github.com/shoya-sue" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fab fa-github text-xl"></i>
                            </a>
                            <span class="text-xs text-gray-500">© 2024 Game Collection</span>
                        </div>
                    </div>
                </div>
                
                {{-- 記事一覧セクション --}}
                <div class="mt-4">
                    <h3 class="text-lg font-bold mb-3 text-purple-300 flex items-center">
                        <i class="fas fa-list mr-2"></i>記事一覧
                        <span class="ml-2 text-sm text-gray-400">(全{{ $pages->count() }}件)</span>
                    </h3>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($pages as $article)
                        <article class="bg-black/40 backdrop-blur-lg rounded-lg border border-purple-500/20 hover:border-purple-400/40 transition-all card-hover">
                            <a href="{{ route('notion.show', $article->id) }}" class="block p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-bold text-white text-sm line-clamp-2 flex-1">
                                        {{ $article->title ?? 'Untitled' }}
                                    </h4>
                                </div>
                                
                                @if($article->category)
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @if(is_array($article->category))
                                            @foreach($article->category as $cat)
                                                <span class="inline-block px-2 py-0.5 text-xs gradient-gaming text-white rounded-full">
                                                    {{ $cat }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-xs gradient-gaming text-white rounded-full">
                                                {{ $article->category }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($article->description)
                                    <p class="text-xs text-gray-400 line-clamp-2 mb-2">
                                        {{ Str::limit($article->description, 80) }}
                                    </p>
                                @endif
                                
                                @if(isset($article->tags) && count($article->tags) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($article->tags, 0, 3) as $tag)
                                            <span class="inline-block px-2 py-0.5 text-xs bg-purple-600/20 text-purple-300 rounded">
                                                #{{ $tag }}
                                            </span>
                                        @endforeach
                                        @if(count($article->tags) > 3)
                                            <span class="text-xs text-gray-500">+{{ count($article->tags) - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </a>
                        </article>
                        @endforeach
                    </div>
                </div>
                
                @if($pages->isEmpty())
                <div class="bg-yellow-500/20 backdrop-blur border border-yellow-500/50 rounded-lg p-6">
                    <p class="text-yellow-300">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Notionデータベースからページを取得できませんでした。
                        .envファイルの設定を確認してください。
                    </p>
                </div>
                @endif
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