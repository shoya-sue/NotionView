<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notion Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <h2 class="text-lg font-semibold mb-4">ページ一覧</h2>
            <ul class="space-y-2">
                @foreach($pages as $page)
                    <li>
                        <a href="{{ route('notion.show', $page->id) }}" 
                           class="block p-3 rounded hover:bg-gray-700 transition-colors">
                            {{ $page->title ?? 'Untitled' }}
                            @if($page->description)
                                <span class="block text-sm text-gray-400 mt-1">{{ Str::limit($page->description, 50) }}</span>
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
                    <h2 class="text-3xl font-bold mb-6">Notion Viewerへようこそ</h2>
                    <p class="text-gray-600 mb-4">
                        このシステムでは、Notionで作成したページを簡単に閲覧できます。
                    </p>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-blue-700">
                            左のメニューからページを選択してください。
                        </p>
                    </div>
                    
                    <h3 class="text-xl font-semibold mb-4">使い方</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>左側のサイドバーに表示されているページ一覧から、閲覧したいページをクリック</li>
                        <li>選択したページの内容がこのエリアに表示されます</li>
                        <li>Notion APIの設定が完了していれば、リアルタイムでNotionの内容が反映されます</li>
                    </ol>
                    
                    @if(!env('NOTION_API_TOKEN') || env('NOTION_API_TOKEN') === 'your_integration_token_here')
                        <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-yellow-700">
                                <strong>注意:</strong> Notion APIトークンが設定されていません。
                                現在はサンプルデータが表示されています。
                            </p>
                            <p class="text-yellow-700 mt-2">
                                実際のNotionコンテンツを表示するには、.envファイルに以下を設定してください：
                            </p>
                            <pre class="bg-gray-800 text-white p-2 mt-2 rounded text-sm">NOTION_API_TOKEN=your_integration_token
NOTION_DATABASE_ID=your_database_id</pre>
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