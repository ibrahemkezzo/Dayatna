<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_range' => 'nullable|string|max:100',

            // Images Validation
            'images' => 'nullable|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB per image

            // Contacts
            'contacts' => 'nullable|array',
            'contacts.*.type' => 'required|string|in:phone,whatsapp,facebook,instagram,email',
            'contacts.*.value' => 'required|string|max:255',

            // Working Hours
            'working_hours' => 'required|array|size:7',
            'working_hours.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'working_hours.*.is_closed' => 'required|boolean',
            'working_hours.*.open_time' => 'required_if:working_hours.*.is_closed,false|nullable|date_format:H:i',
            'working_hours.*.close_time' => 'required_if:working_hours.*.is_closed,false|nullable|date_format:H:i',
        ];
    }

    /**
     * Define body parameters for Scribe documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The commercial or official name of the entity.',
                'example' => 'Al-Diyafa Green Stadium',
            ],
            'category_id' => [
                'description' => 'The ID of the specific subcategory (must exist in the categories table).',
                'example' => 1,
            ],
            'description' => [
                'description' => 'Detailed text describing the entity services, ambiance, or specialties.',
                'example' => 'Premium natural grass football field with full lighting facilities.',
            ],
            'address' => [
                'description' => 'The descriptive physical address or landmark of the location.',
                'example' => 'An-Nabk, Green District',
            ],
            'latitude' => [
                'description' => 'The precise GPS latitude coordinate for map rendering.',
                'example' => 34.0200,
            ],
            'longitude' => [
                'description' => 'The precise GPS longitude coordinate for map rendering.',
                'example' => 36.7500,
            ],
            'price_range' => [
                'description' => 'An indicative pricing scale or explicitly formatted cost.',
                'example' => '50k - 80k SYP/Hour',
            ],
            'images' => [
                'description' => 'An array of image files to populate the entity gallery.',
                'required' => false,
                'type' => 'file[]',
            ],
            'contacts' => [
                'description' => 'List of social and communication channels associated with the entity.',
                'example' => [
                    ['type' => 'whatsapp', 'value' => '+963930000000'],
                    ['type' => 'phone', 'value' => '+96311123456']
                ]
            ],
            'contacts.*.type' => [
                'description' => 'The type of communication channel.',
                'required' => true,
            ],
            'contacts.*.value' => [
                'description' => 'The actual phone number, username, or profile URL.',
                'required' => true,
            ],
            'working_hours' => [
                'description' => 'Strict chronological schedule array containing exactly 7 elements representing the week days.',
                'example' => [
                    ['day_of_week' => 0, 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '23:30'],
                    ['day_of_week' => 1, 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '23:30'],
                    ['day_of_week' => 2, 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '23:30'],
                    ['day_of_week' => 3, 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '23:30'],
                    ['day_of_week' => 4, 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '23:30'],
                    ['day_of_week' => 5, 'is_closed' => false, 'open_time' => '14:00', 'close_time' => '00:00'],
                    ['day_of_week' => 6, 'is_closed' => true, 'open_time' => null, 'close_time' => null],
                ]
            ],
            'working_hours.*.day_of_week' => [
                'description' => 'Day representation index (0 for Sunday, 6 for Saturday). Must be distinct inside the array.',
                'required' => true,
            ],
            'working_hours.*.is_closed' => [
                'description' => 'Determines if the entity is fully closed or on a weekend break on this day.',
                'required' => true,
            ],
            'working_hours.*.open_time' => [
                'description' => 'The opening time in 24-hour (HH:mm) format. Required if is_closed is false.',
                'required' => false,
            ],
            'working_hours.*.close_time' => [
                'description' => 'The closing time in 24-hour (HH:mm) format. Required if is_closed is false.',
                'required' => false,
            ],
        ];
    }
}
