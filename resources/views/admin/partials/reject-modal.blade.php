<div
    id="reject-ebook-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    style="background-color: rgba(27, 21, 16, 0.72); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
>
    <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-beige bg-cream p-6 shadow-2xl sm:p-7">
        <div class="flex items-start justify-between border-b border-sand pb-4">
            <div>
                <span class="inline-block rounded-md bg-sand px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-espresso">Phản hồi kiểm duyệt</span>
                <h3 class="mt-1.5 font-serif text-2xl font-bold text-espresso">Từ chối bản thảo</h3>
            </div>
            <button
                type="button"
                onclick="window.rvCloseRejectModal()"
                data-close-reject-modal="true"
                class="inline-flex h-8 items-center justify-center rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:bg-sand hover:text-espresso"
            >
                Đóng
            </button>
        </div>

        <form id="reject-ebook-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div class="rounded-2xl border border-beige bg-white p-3.5 text-xs">
                <p class="text-mocha">Tác phẩm: <strong id="reject-modal-book-title" class="font-serif text-base text-espresso"></strong></p>
                <p class="mt-0.5 text-mocha">Người gửi: <strong id="reject-modal-author-name" class="text-espresso"></strong></p>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Lý do từ chối *</label>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <button type="button" onclick="window.rvPickRejectReason(this)" data-reason-chip="Bản thảo PDF lỗi định dạng hoặc thiếu trang" class="rounded-lg border border-beige bg-white px-3 py-1.5 text-[11px] font-medium text-espresso transition hover:bg-sand">
                        Lỗi định dạng PDF
                    </button>
                    <button type="button" onclick="window.rvPickRejectReason(this)" data-reason-chip="Ảnh bìa chất lượng thấp hoặc không khớp nội dung" class="rounded-lg border border-beige bg-white px-3 py-1.5 text-[11px] font-medium text-espresso transition hover:bg-sand">
                        Ảnh bìa chưa đạt
                    </button>
                    <button type="button" onclick="window.rvPickRejectReason(this)" data-reason-chip="Mô tả nội dung sơ sài, cần bổ sung chi tiết" class="rounded-lg border border-beige bg-white px-3 py-1.5 text-[11px] font-medium text-espresso transition hover:bg-sand">
                        Mô tả quá ngắn
                    </button>
                    <button type="button" onclick="window.rvPickRejectReason(this)" data-reason-chip="Thông tin tác giả hoặc bản quyền cần xác minh" class="rounded-lg border border-beige bg-white px-3 py-1.5 text-[11px] font-medium text-espresso transition hover:bg-sand">
                        Cần xác minh tác giả
                    </button>
                </div>
                <input
                    id="reject-reason-input"
                    type="text"
                    name="rejection_reason"
                    required
                    placeholder="Nhập lý do từ chối chính..."
                    class="mt-2 w-full rounded-xl border border-beige bg-white px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:ring-espresso"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Nội dung phản hồi gửi tác giả *</label>
                <textarea
                    id="reject-note-input"
                    name="rejection_note"
                    rows="4"
                    required
                    placeholder="Ghi rõ các điểm tác giả cần điều chỉnh để gửi duyệt lại..."
                    class="mt-1.5 w-full rounded-xl border border-beige bg-white px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:ring-espresso"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 border-t border-sand pt-4">
                <button
                    type="button"
                    onclick="window.rvCloseRejectModal()"
                    data-close-reject-modal="true"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-beige bg-white px-5 text-xs font-semibold text-espresso transition hover:bg-sand"
                >
                    Hủy
                </button>
                <button
                    type="submit"
                    class="inline-flex h-9 items-center justify-center rounded-xl bg-espresso px-5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha"
                >
                    Gửi phản hồi từ chối
                </button>
            </div>
        </form>
    </div>
</div>
<script>
window.rvOpenRejectModal = function(btn) {
    var modal = document.getElementById('reject-ebook-modal');
    var form = document.getElementById('reject-ebook-form');
    var titleEl = document.getElementById('reject-modal-book-title');
    var authorEl = document.getElementById('reject-modal-author-name');
    var reasonInput = document.getElementById('reject-reason-input');
    var noteInput = document.getElementById('reject-note-input');
    if (!modal || !form || !btn) return;
    form.action = btn.getAttribute('data-reject-action') || '';
    if (titleEl) titleEl.textContent = btn.getAttribute('data-ebook-title') || '';
    if (authorEl) authorEl.textContent = btn.getAttribute('data-author-name') || '';
    if (reasonInput) reasonInput.value = '';
    if (noteInput) noteInput.value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if (reasonInput) setTimeout(function() { reasonInput.focus(); }, 50);
};
window.rvCloseRejectModal = function() {
    var modal = document.getElementById('reject-ebook-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};
window.rvPickRejectReason = function(chip) {
    var reason = chip.getAttribute('data-reason-chip') || '';
    var reasonInput = document.getElementById('reject-reason-input');
    var noteInput = document.getElementById('reject-note-input');
    if (reasonInput) reasonInput.value = reason;
    if (noteInput && !noteInput.value.trim()) {
        noteInput.value = 'Bản thảo cần điều chỉnh thêm về mục: ' + reason + '. Vui lòng cập nhật lại thông tin và gửi duyệt lại.';
    }
};
</script>
