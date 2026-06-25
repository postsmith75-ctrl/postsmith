{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0">
    <channel>
        <title>PostSmith Posts - {{ $user->name ?: $user->email }}</title>
        <link>{{ route('dashboard') }}</link>
        <description>Saved PostSmith posts for scheduler import.</description>
        @foreach ($posts as $post)
            @php
                $postText = str_replace(']]>', ']]]]><![CDATA[>', $post->post_text ?? '');
            @endphp
            <item>
                <title>{{ $post->driver ?: 'PostSmith post' }} for {{ $post->platform ?: 'social' }}</title>
                <link>{{ route('dashboard') }}#tracker</link>
                <guid isPermaLink="false">postsmith-post-{{ $post->id }}</guid>
                <pubDate>{{ $post->created_at?->toRfc2822String() }}</pubDate>
                <description><![CDATA[{!! $postText !!}]]></description>
            </item>
        @endforeach
    </channel>
</rss>
