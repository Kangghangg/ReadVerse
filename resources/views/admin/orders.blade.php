@extends('layouts.readverse')

@section('title', 'Quản trị hệ thống - Đơn hàng & Doanh thu')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold text-espresso">Đơn hàng & Thống kê Doanh thu</h1>
            <p class="mt-0.5 text-xs text-mocha">Theo dõi biểu đồ doanh thu, lịch sử thanh toán mở khóa sách và xuất tệp báo cáo</p>
        </div>

        <a
            href="{{ route('admin.orders.export') }}"
            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha"
        >
            Xuất báo cáo CSV / Excel
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-beige bg-sand/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-espresso">Tổng doanh thu</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }} đ</p>
        </div>
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Tổng số đơn hàng</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['total_orders'] ?? 0 }} đơn</p>
        </div>
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Độc giả đã mua sách</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['unique_buyers'] ?? 0 }} người</p>
        </div>
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Giá trị đơn trung bình</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ number_format($stats['avg_order_value'] ?? 0, 0, ',', '.') }} đ</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm lg:col-span-2">
            <div class="border-b border-sand pb-3">
                <h2 class="font-serif text-xl font-bold text-espresso">Biểu đồ Doanh thu & Số lượng Đơn hàng theo Ngày</h2>
                <p class="text-xs text-mocha">Thống kê giá trị giao dịch theo thời gian</p>
            </div>
            <div class="mt-4 h-72 w-full">
                <canvas id="revenueDailyChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <div class="border-b border-sand pb-3">
                <h2 class="font-serif text-xl font-bold text-espresso">Cơ cấu Doanh thu theo Thể loại</h2>
                <p class="text-xs text-mocha">Tỷ trọng doanh thu của từng nhóm sách</p>
            </div>
            <div class="mt-4 flex h-72 w-full items-center justify-center">
                <canvas id="revenueCategoryChart"></canvas>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-6 grid grid-cols-1 gap-3 rounded-2xl border border-beige bg-white p-4 shadow-sm sm:grid-cols-5">
        <input type="text" name="search" value="{{ $search }}" placeholder="Mã đơn, tên độc giả, tên sách..." class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs text-ink placeholder:text-taupe sm:col-span-2">
        <input type="date" name="date_from" value="{{ $dateFrom }}" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs text-espresso">
        <input type="date" name="date_to" value="{{ $dateTo }}" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs text-espresso">
        <div class="flex gap-2">
            <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center rounded-xl bg-espresso px-4 text-xs font-semibold text-cream transition hover:bg-mocha">Lọc đơn</button>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-beige bg-cream px-3.5 text-xs font-semibold text-espresso transition hover:bg-sand">Đặt lại</a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                    <tr>
                        <th class="px-5 py-3.5">Mã đơn & Thời gian</th>
                        <th class="px-5 py-3.5">Độc giả</th>
                        <th class="px-5 py-3.5">Tác phẩm</th>
                        <th class="px-5 py-3.5">Phương thức</th>
                        <th class="px-5 py-3.5">Số tiền</th>
                        <th class="px-5 py-3.5 text-right">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md border border-beige/70 bg-sand/60 px-2.5 py-1 font-mono text-xs font-bold text-espresso">{{ $order->order_code }}</span>
                                <p class="mt-1 text-xs text-taupe">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <p class="font-semibold text-espresso">{{ $order->user?->name ?? 'Độc giả' }}</p>
                                <p class="mt-0.5 text-xs text-mocha">{{ $order->user?->email }}</p>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                @if($order->ebook)
                                    <p class="font-serif text-base font-bold text-espresso">{{ $order->ebook->title }}</p>
                                    <p class="mt-0.5 text-xs text-mocha">{{ $order->ebook->category?->name }} • {{ $order->ebook->author?->name }}</p>
                                @else
                                    <span class="text-xs italic text-taupe">Tác phẩm đã ngừng lưu hành</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige bg-cream px-2.5 py-1 text-xs font-semibold uppercase text-espresso">
                                    {{ $order->payment_method ?? 'qr_transfer' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <span class="font-bold text-espresso">{{ number_format((float) $order->amount, 0, ',', '.') }} đ</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-right whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-espresso/20 bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                                    Hoàn tất
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-mocha">
                                Chưa có đơn hàng nào khớp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $orders->links('vendor.pagination.readverse') }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var dailyCtx = document.getElementById('revenueDailyChart');
    if (dailyCtx && window.Chart) {
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: @json($chartDailyLabels),
                datasets: [
                    {
                        type: 'line',
                        label: 'Doanh thu (VNĐ)',
                        data: @json($chartDailyRevenue),
                        borderColor: '#4B3828',
                        backgroundColor: 'rgba(75, 56, 40, 0.14)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Số đơn hàng',
                        data: @json($chartDailyCount),
                        backgroundColor: 'rgba(169, 134, 96, 0.65)',
                        borderRadius: 6,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        ticks: {
                            callback: function(v) { return v.toLocaleString('vi-VN') + ' đ'; }
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    var catCtx = document.getElementById('revenueCategoryChart');
    if (catCtx && window.Chart) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartCategoryLabels),
                datasets: [{
                    data: @json($chartCategoryValues),
                    backgroundColor: ['#4B3828', '#8A6A4E', '#A98660', '#C3A27D', '#D8C5AA', '#6B4F3A'],
                    borderWidth: 2,
                    borderColor: '#FFFDF9'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endsection
