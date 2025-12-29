@php
    // Handle both infolist ViewEntry (uses $getState()) and direct modal view (uses $contents)
    $contentItems = $contents ?? (isset($getState) ? $getState() : collect());
@endphp

<div class="entry-preview space-y-6">
    @forelse($contentItems as $content)
        <div class="content-block rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div
                class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ $content->type }}
                </span>
                @if($content->order > 0)
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        #{{ $content->order }}
                    </span>
                @endif
            </div>
            <div class="p-4 bg-white dark:bg-gray-900">
                @switch($content->type)
                    @case('markdown')
                        <div class="prose dark:prose-invert max-w-none">
                            {!! \Illuminate\Support\Str::markdown($content->body ?? '') !!}
                        </div>
                        @break

                    @case('html')
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $content->body !!}
                        </div>
                        @break

                    @case('json')
                        <pre
                            class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg overflow-x-auto text-sm font-mono text-gray-800 dark:text-gray-200"><code>{{ json_encode(json_decode($content->body ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        @break

                    @case('text')
                        <div
                            class="whitespace-pre-wrap font-mono text-sm text-gray-700 dark:text-gray-300">{{ $content->body }}</div>
                        @break

                    @default
                        <div class="text-gray-500 dark:text-gray-400 italic">
                            Unknown content type: {{ $content->type }}
                        </div>
                @endswitch
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-document class="w-12 h-12 mx-auto mb-2 opacity-50"/>
            <p>No content blocks available for preview.</p>
        </div>
    @endforelse
</div>
