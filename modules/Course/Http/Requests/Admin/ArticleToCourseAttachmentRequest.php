<?php

declare(strict_types=1);

namespace Modules\Course\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Article To Course Attachment Request.
 *
 * @package Modules\Course\Http\Requests\Admin
 */
class ArticleToCourseAttachmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'wp_article_id' => ['required'],
            'days'          => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
