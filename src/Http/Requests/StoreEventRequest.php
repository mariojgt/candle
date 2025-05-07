<?php

namespace Mariojgt\Candle\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Since this is public tracking, we allow all requests through.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * This ensures the API receives expected structure and values.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_key' => 'nullable|string',
            'domain' => 'nullable|string',
            'events' => 'required|array|min:1',
            'events.*.event_name' => 'sometimes|string|max:255',
            'events.*.session_id' => 'sometimes|string|max:255',
            'events.*.user_id' => 'sometimes|string|max:255',
            'events.*.url' => 'sometimes|url',
            'events.*.referrer' => 'nullable|string|max:2048',
            'events.*.screen_width' => 'nullable|integer',
            'events.*.screen_height' => 'nullable|integer',
            'events.*.language' => 'nullable|string|max:10',
            'events.*.properties' => 'nullable|array',
        ];
    }

    /**
     * Custom error messages (optional).
     *
     * @return array
     */
    public function messages()
    {
        return [
            'events.required' => 'At least one event must be submitted.',
            'events.array' => 'The events field must be an array.',
        ];
    }
}
