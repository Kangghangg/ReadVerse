<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && !$this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email') && is_string($this->input('email'))) {
            $this->merge([
                'email' => mb_strtolower(trim($this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'avatar' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:10240'],
            'avatar_base64' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'avatar.file' => 'Tệp tải lên không hợp lệ.',
            'avatar.image' => 'Tệp tải lên phải là định dạng hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ hỗ trợ định dạng JPG, JPEG, PNG, WEBP, GIF hoặc AVIF.',
            'avatar.max' => 'Kích thước ảnh đại diện tối đa là 8MB.',
            'avatar.uploaded' => 'Không thể tải ảnh lên máy chủ. Vui lòng chọn ảnh có dung lượng nhỏ hơn 8MB.',
        ];
    }
}
