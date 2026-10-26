@extends('layouts.readverse')

@section('title', 'Hồ sơ cá nhân - ReadVerse')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-3xl border border-beige bg-white shadow-sm">
        <div class="bg-espresso px-6 py-8 text-cream sm:px-10">
            <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-col items-center gap-5 sm:flex-row">
                    <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-full border-4 border-cream bg-sand shadow-lg">
                        <img id="avatar-preview-img" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="text-center sm:text-left">
                        <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <span class="rounded-full bg-cream/20 px-3 py-0.5 text-xs font-semibold uppercase tracking-wider text-cream">
                                {{ $user->isAuthor() ? 'Tác giả' : 'Độc giả' }}
                            </span>
                            <span class="text-xs text-sand/90">Tham gia {{ $user->created_at?->format('d/m/Y') }}</span>
                        </div>
                        <h1 class="mt-1.5 font-serif text-3xl font-bold text-cream sm:text-4xl">{{ $user->name }}</h1>
                        <p class="text-sm text-sand/90">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @if($user->isAuthor())
                        <a href="{{ route('author.ebooks.index') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-cream px-4 text-xs font-semibold text-espresso shadow-sm transition hover:bg-sand">
                            Quản lý tác phẩm ({{ $stats['total_uploaded'] ?? 0 }})
                        </a>
                    @endif
                    <a href="{{ url('/my-library') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-cream/40 bg-white/10 px-4 text-xs font-semibold text-cream transition hover:bg-white/20">
                        Tủ sách cá nhân
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 divide-x divide-y divide-sand border-t border-sand bg-cream/50 sm:grid-cols-4 sm:divide-y-0">
            @if($user->isAuthor())
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Tác phẩm đã xuất bản</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['published_count'] ?? 0 }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Chờ duyệt / Cần sửa</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ ($stats['pending_count'] ?? 0) }} / {{ $stats['rejected_count'] ?? 0 }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Tổng lượt đọc</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ number_format($stats['total_views'] ?? 0) }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Sách đã sở hữu</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['unlocked_count'] ?? 0 }}</p>
                </div>
            @else
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Sách đang đọc</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['reading_count'] ?? 0 }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Sách yêu thích</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['favorites_count'] ?? 0 }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Sách đã mở khóa</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['unlocked_count'] ?? 0 }}</p>
                </div>
                <div class="p-4 text-center sm:p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-mocha">Đánh giá đã viết</p>
                    <p class="mt-1 font-serif text-2xl font-bold text-espresso">{{ $stats['reviews_count'] ?? 0 }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            <div class="rounded-3xl border border-beige bg-white p-6 shadow-sm sm:p-8">
                <div class="border-b border-sand pb-4">
                    <h2 class="font-serif text-2xl font-bold text-espresso">Thông tin Hồ sơ & Liên hệ</h2>
                    <p class="mt-1 text-xs text-mocha">Cập nhật thông tin cá nhân, ảnh đại diện và tiểu sử hiển thị trên ReadVerse.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Ảnh đại diện</label>
                        <div class="mt-2 flex flex-wrap items-center gap-4">
                            <input
                                type="file"
                                name="avatar"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                onchange="if(this.files && this.files[0]){ document.getElementById('avatar-preview-img').src = URL.createObjectURL(this.files[0]); }"
                                class="block w-full max-w-xs text-xs text-mocha file:mr-3 file:rounded-xl file:border-0 file:bg-sand file:px-4 file:py-2 file:text-xs file:font-semibold file:text-espresso hover:file:bg-beige"
                            >
                            <span class="text-xs text-taupe">Hỗ trợ JPG, PNG, WEBP (Tối đa 4MB)</span>
                        </div>
                        <x-input-error class="mt-1.5" :messages="$errors->get('avatar')" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-espresso">
                                {{ $user->isAuthor() ? 'Họ tên / Bút danh *' : 'Họ và tên *' }}
                            </label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Địa chỉ Email *</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Số điện thoại</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $profile->phone) }}" placeholder="VD: 0901234567" class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:bg-white focus:ring-espresso">
                        </div>

                        <div>
                            <label for="website" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Website cá nhân</label>
                            <input id="website" name="website" type="text" value="{{ old('website', $profile->website) }}" placeholder="https://..." class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:bg-white focus:ring-espresso">
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Địa chỉ liên hệ</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $profile->address) }}" placeholder="VD: TP. Hồ Chí Minh" class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:bg-white focus:ring-espresso">
                    </div>

                    <div>
                        <label for="bio" class="block text-xs font-semibold uppercase tracking-wider text-espresso">
                            {{ $user->isAuthor() ? 'Tiểu sử Tác giả' : 'Giới thiệu bản thân' }}
                        </label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Chia sẻ đôi nét về bản thân bạn..." class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:bg-white focus:ring-espresso">{{ old('bio', $profile->bio) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-sand pt-4">
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-espresso px-6 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                            Lưu thay đổi hồ sơ
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-beige bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between border-b border-sand pb-4">
                    <div>
                        <h2 class="font-serif text-2xl font-bold text-espresso">Lịch sử Giao dịch & Mở khóa Sách</h2>
                        <p class="mt-0.5 text-xs text-mocha">Các tác phẩm bạn đã thanh toán mở khóa trên hệ thống</p>
                    </div>
                    <a href="{{ route('cart.index') }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-sand/50 px-3.5 text-xs font-semibold text-espresso transition hover:bg-sand">
                        Xem giỏ hàng
                    </a>
                </div>

                @if(count($recentOrders) > 0)
                    <div class="mt-4 divide-y divide-sand">
                        @foreach($recentOrders as $order)
                            <div class="flex flex-wrap items-center justify-between gap-4 py-3.5 text-sm">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-sand px-2 py-0.5 font-mono text-[11px] font-semibold text-espresso">{{ $order->order_code }}</span>
                                        <span class="text-xs text-taupe">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($order->ebook)
                                        <a href="{{ route('ebooks.show', $order->ebook->slug) }}" class="mt-1 block truncate font-serif text-base font-bold text-espresso hover:text-mocha">
                                            {{ $order->ebook->title }}
                                        </a>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold text-espresso">{{ number_format((float) $order->amount, 0, ',', '.') }} đ</span>
                                    @if($order->ebook)
                                        <a href="{{ route('ebooks.read', $order->ebook->slug) }}" class="inline-flex h-8 items-center justify-center rounded-lg bg-espresso px-3 text-xs font-semibold text-cream hover:bg-mocha">
                                            Đọc sách
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-beige bg-cream/40 py-8 text-center text-xs text-mocha">
                        Bạn chưa có giao dịch mua sách nào.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-8">
            <div class="rounded-3xl border border-beige bg-white p-6 shadow-sm sm:p-7">
                <div class="border-b border-sand pb-4">
                    <h2 class="font-serif text-2xl font-bold text-espresso">Đổi mật khẩu</h2>
                    <p class="mt-1 text-xs text-mocha">Sử dụng mật khẩu mạnh để bảo vệ tài khoản của bạn</p>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="mt-4 rounded-xl border border-beige bg-sand/70 px-4 py-3 text-xs font-semibold text-espresso">
                        Đã cập nhật mật khẩu mới thành công.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="update_password_current_password" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Mật khẩu hiện tại</label>
                        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Mật khẩu mới</label>
                        <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Xác nhận mật khẩu mới</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-espresso text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                            Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
