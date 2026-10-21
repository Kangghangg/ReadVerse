@extends('layouts.readverse')

@section('title', 'Kiểm duyệt bản thảo - ReadVerse Admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div id="catalog-container">
        <h2 class="font-serif text-2xl font-semibold text-espresso">Danh sách bản thảo đang chờ phê duyệt ({{ count($ebooks) }})</h2>
        <div id="catalog-grid-wrapper" class="mt-4 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm transition-opacity duration-150">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase text-mocha">
                    <tr>
                        <th class="px-6 py-4">Tác phẩm</th>
                        <th class="px-6 py-4">Tác giả</th>
                        <th class="px-6 py-4">Người tải lên</th>
                        <th class="px-6 py-4">Tệp PDF</th>
                        <th class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($ebooks as $ebook)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-espresso">{{ $ebook->title }}</div>
                            <div class="text-xs text-mocha">{{ $ebook->category?->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-ink">{{ $ebook->author?->name }}</td>
                        <td class="px-6 py-4 text-ink">{{ $ebook->uploader?->name }} ({{ $ebook->uploader?->email }})</td>
                        <td class="px-6 py-4">
                            <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="text-xs font-semibold text-espresso underline hover:text-mocha">Xem tệp PDF</a>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.ebooks.approve', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">Duyệt phát hành</button>
                            </form>
                            <form action="{{ route('admin.ebooks.reject', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Từ chối bản thảo" data-confirm="Bạn có chắc chắn muốn từ chối phát hành bản thảo &laquo;{{ $ebook->title }}&raquo; không?" data-confirm-btn="Từ chối" class="ml-1 inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Từ chối</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-mocha">Hiện không có bản thảo nào đang chờ kiểm duyệt.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
