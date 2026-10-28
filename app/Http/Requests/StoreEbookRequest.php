<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreEbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->isAuthor() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:5'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'pages' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'pre_uploaded_file' => ['nullable', 'string', 'max:500'],
            'pre_uploaded_cover' => ['nullable', 'string', 'max:500', 'regex:#^ebooks/covers/[A-Za-z0-9_.\-]+$#'],
            'cover_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,svg', 'max:20480'],
            'file_path' => [
                'required_without:pre_uploaded_file',
                'nullable',
                'file',
                'max:204800',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!$value instanceof UploadedFile) {
                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'epub'], true)) {
                        $fail('Tệp sách tải lên phải có định dạng .PDF hoặc .EPUB.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề sách.',
            'description.required' => 'Vui lòng nhập mô tả nội dung sách.',
            'description.min' => 'Mô tả sách cần tối thiểu 5 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục thể loại sách.',
            'file_path.required_without' => 'Vui lòng chọn tệp sách PDF để tải lên.',
            'file_path.uploaded' => 'Tệp PDF đang được tải lên, vui lòng đợi thanh tiến độ đạt 100% rồi bấm gửi.',
        ];
    }
}
