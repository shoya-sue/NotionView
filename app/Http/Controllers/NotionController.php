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
        $this->notionToken = config('services.notion.token');
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
        $databaseId = config('services.notion.database_id');
        
        if (!$databaseId) {
            // データベースIDが設定されていない場合はサンプルデータを返す
            return collect([
                (object)[
                    'id' => 'sample1', 
                    'title' => 'サンプルページ1', 
                    'description' => 'これはサンプルページです',
                    'category' => 'チュートリアル',
                    'tags' => ['基本', 'サンプル'],
                    'status' => '公開',
                    'sort_no' => 10,
                ],
                (object)[
                    'id' => 'sample2', 
                    'title' => 'サンプルページ2', 
                    'description' => 'これもサンプルページです',
                    'category' => 'ドキュメント',
                    'tags' => ['API', 'ガイド'],
                    'status' => '下書き',
                    'sort_no' => 20,
                ],
                (object)[
                    'id' => 'sample3', 
                    'title' => 'サンプルページ3', 
                    'description' => 'さらにサンプルページです',
                    'category' => 'チュートリアル',
                    'tags' => ['応用'],
                    'status' => '公開',
                    'sort_no' => 30,
                ],
            ])->sortBy('sort_no')->values();
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
                        $properties = $page['properties'] ?? [];
                        
                        return (object)[
                            'id' => $page['id'],
                            'title' => $this->extractTitle($page),
                            'description' => $this->extractProperty($page, 'Description') 
                                          ?? $this->extractProperty($page, '説明'),
                            'category' => $this->extractMultiSelectProperty($properties, 'カテゴリー')
                                        ?? $this->extractSelectProperty($properties, 'Category'),
                            'tags' => $this->extractMultiSelectProperty($properties, 'タグ')
                                    ?? $this->extractMultiSelectProperty($properties, 'Tags'),
                            'status' => $this->extractSelectProperty($properties, 'ステータス')
                                      ?? $this->extractSelectProperty($properties, 'Status'),
                            'sort_no' => $this->extractNumberProperty($properties, 'SortNo')
                                      ?? $this->extractNumberProperty($properties, '並び順')
                                      ?? 999999,
                            'created_time' => $page['created_time'] ?? null,
                            'last_edited_time' => $page['last_edited_time'] ?? null,
                        ];
                    })->sortBy('sort_no')->values();
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
            $sampleData = [
                'sample1' => [
                    'title' => 'サンプルページ1',
                    'category' => 'チュートリアル',
                    'tags' => ['基本', 'サンプル'],
                    'status' => '公開',
                ],
                'sample2' => [
                    'title' => 'サンプルページ2',
                    'category' => 'ドキュメント',
                    'tags' => ['API', 'ガイド'],
                    'status' => '下書き',
                ],
                'sample3' => [
                    'title' => 'サンプルページ3',
                    'category' => 'チュートリアル',
                    'tags' => ['応用'],
                    'status' => '公開',
                ],
            ];
            
            $data = $sampleData[$pageId] ?? ['title' => 'サンプルページ', 'category' => null, 'tags' => [], 'status' => null];
            
            return (object)[
                'id' => $pageId,
                'title' => $data['title'],
                'content' => 'これはサンプルページのコンテンツです。',
                'category' => $data['category'],
                'tags' => $data['tags'],
                'status' => $data['status'],
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
                    $properties = $page['properties'] ?? [];
                    
                    return (object)[
                        'id' => $page['id'],
                        'title' => $this->extractTitle($page),
                        'content' => '',
                        'category' => $this->extractMultiSelectProperty($properties, 'カテゴリー')
                                    ?? $this->extractSelectProperty($properties, 'Category'),
                        'tags' => $this->extractMultiSelectProperty($properties, 'タグ')
                                ?? $this->extractMultiSelectProperty($properties, 'Tags'),
                        'status' => $this->extractSelectProperty($properties, 'ステータス')
                                  ?? $this->extractSelectProperty($properties, 'Status'),
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
        // 日本語のプロパティ名を優先的にチェック
        if (isset($page['properties']['ドキュメント名']['title'][0]['plain_text'])) {
            return $page['properties']['ドキュメント名']['title'][0]['plain_text'];
        }
        
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
    
    private function extractSelectProperty($properties, $propertyName)
    {
        if (isset($properties[$propertyName]['select']['name'])) {
            return $properties[$propertyName]['select']['name'];
        }
        
        return null;
    }
    
    private function extractMultiSelectProperty($properties, $propertyName)
    {
        if (isset($properties[$propertyName]['multi_select'])) {
            return collect($properties[$propertyName]['multi_select'])->pluck('name')->toArray();
        }
        
        return [];
    }
    
    private function extractNumberProperty($properties, $propertyName)
    {
        if (isset($properties[$propertyName]['number'])) {
            return $properties[$propertyName]['number'];
        }
        
        return null;
    }
}