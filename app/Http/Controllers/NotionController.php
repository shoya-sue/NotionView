<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NotionController extends Controller
{
    private $notionToken;
    private $notionVersion = '2022-06-28';
    
    public function __construct()
    {
        $this->notionToken = env('NOTION_API_TOKEN');
    }
    
    public function index()
    {
        $pages = $this->fetchPages();
        
        return view('notion.index', compact('pages'));
    }
    
    public function show($pageId)
    {
        $page = $this->fetchPage($pageId);
        $blocks = $this->fetchPageBlocks($pageId);
        $pages = $this->fetchPages();
        
        return view('notion.show', compact('page', 'blocks', 'pages', 'pageId'));
    }
    
    private function fetchPages()
    {
        $databaseId = env('NOTION_DATABASE_ID');
        
        if (!$databaseId) {
            // データベースIDが設定されていない場合はサンプルデータを返す
            return collect([
                (object)['id' => 'sample1', 'title' => 'サンプルページ1', 'description' => 'これはサンプルページです'],
                (object)['id' => 'sample2', 'title' => 'サンプルページ2', 'description' => 'これもサンプルページです'],
                (object)['id' => 'sample3', 'title' => 'サンプルページ3', 'description' => 'さらにサンプルページです'],
            ]);
        }
        
        return Cache::remember('notion_pages', 300, function () use ($databaseId) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->notionToken,
                    'Notion-Version' => $this->notionVersion,
                    'Content-Type' => 'application/json',
                ])->post("https://api.notion.com/v1/databases/{$databaseId}/query", [
                    'page_size' => 50,
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data['results'])->map(function ($page) {
                        $title = $this->extractTitle($page);
                        return (object)[
                            'id' => $page['id'],
                            'title' => $title,
                            'description' => $this->extractProperty($page, 'Description'),
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Notion API error: ' . $e->getMessage());
            }
            
            return collect([]);
        });
    }
    
    private function fetchPage($pageId)
    {
        if (str_starts_with($pageId, 'sample')) {
            // サンプルページの場合
            return (object)[
                'id' => $pageId,
                'title' => 'サンプルページ',
                'content' => 'これはサンプルページのコンテンツです。',
            ];
        }
        
        return Cache::remember("notion_page_{$pageId}", 300, function () use ($pageId) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->notionToken,
                    'Notion-Version' => $this->notionVersion,
                ])->get("https://api.notion.com/v1/pages/{$pageId}");
                
                if ($response->successful()) {
                    $page = $response->json();
                    return (object)[
                        'id' => $page['id'],
                        'title' => $this->extractTitle($page),
                        'content' => '',
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Notion API error: ' . $e->getMessage());
            }
            
            return (object)[
                'id' => $pageId,
                'title' => 'ページが見つかりません',
                'content' => '',
            ];
        });
    }
    
    private function fetchPageBlocks($pageId)
    {
        if (str_starts_with($pageId, 'sample')) {
            // サンプルページの場合
            return collect([
                (object)['type' => 'paragraph', 'text' => 'これはサンプルページのコンテンツです。'],
                (object)['type' => 'heading_2', 'text' => 'サンプル見出し'],
                (object)['type' => 'paragraph', 'text' => 'Notion APIを設定すると、実際のNotionコンテンツが表示されます。'],
                (object)['type' => 'bulleted_list_item', 'text' => 'リスト項目1'],
                (object)['type' => 'bulleted_list_item', 'text' => 'リスト項目2'],
                (object)['type' => 'bulleted_list_item', 'text' => 'リスト項目3'],
            ]);
        }
        
        return Cache::remember("notion_blocks_{$pageId}", 300, function () use ($pageId) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->notionToken,
                    'Notion-Version' => $this->notionVersion,
                ])->get("https://api.notion.com/v1/blocks/{$pageId}/children");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data['results'])->map(function ($block) {
                        return $this->parseBlock($block);
                    })->filter();
                }
            } catch (\Exception $e) {
                \Log::error('Notion API error: ' . $e->getMessage());
            }
            
            return collect([]);
        });
    }
    
    private function parseBlock($block)
    {
        $type = $block['type'];
        $content = $block[$type] ?? [];
        
        $text = '';
        if (isset($content['rich_text'])) {
            $text = collect($content['rich_text'])->pluck('plain_text')->implode('');
        } elseif (isset($content['text'])) {
            $text = collect($content['text'])->pluck('plain_text')->implode('');
        }
        
        return (object)[
            'type' => $type,
            'text' => $text,
            'content' => $content,
        ];
    }
    
    private function extractTitle($page)
    {
        if (isset($page['properties']['Name']['title'][0]['plain_text'])) {
            return $page['properties']['Name']['title'][0]['plain_text'];
        }
        
        if (isset($page['properties']['title']['title'][0]['plain_text'])) {
            return $page['properties']['title']['title'][0]['plain_text'];
        }
        
        foreach ($page['properties'] ?? [] as $property) {
            if (isset($property['title'][0]['plain_text'])) {
                return $property['title'][0]['plain_text'];
            }
        }
        
        return 'Untitled';
    }
    
    private function extractProperty($page, $propertyName)
    {
        if (isset($page['properties'][$propertyName])) {
            $property = $page['properties'][$propertyName];
            
            if (isset($property['rich_text'][0]['plain_text'])) {
                return $property['rich_text'][0]['plain_text'];
            }
            
            if (isset($property['title'][0]['plain_text'])) {
                return $property['title'][0]['plain_text'];
            }
        }
        
        return '';
    }
}