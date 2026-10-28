<?php

namespace App\Http\Requests;

use App\Models\Ebook;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ebookId = $this->route('id') ?? $this->route('ebook');
        $ebook = $ebookId instanceof Ebook ? $ebookId : Ebook::find($ebookId);

        if (!$ebook || !$this->user()) {
            return false;
        }

        return $this->user()->can('update', $ebook);
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
            'file_path' => ['nullable', 'file', 'max:204800'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề sách.',
            'description.required' => 'Vui lòng nhập mô tả nội dung sách.',
            'description.min' => 'Mô tả sách cần tối thiểu 5 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục thể loại sách.',
        ];
    }
}
