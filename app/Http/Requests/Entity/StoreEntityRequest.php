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
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'address'     => 'required|string|max:500',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'price_range' => 'nullable|string|max:100',
            'status'      => 'sometimes|required|string|in:pending,approved,rejected',
            'is_verified' => 'sometimes|required|boolean',

            // حقل إسناد الملكية لمستخدم معين (متاح للآدمن فقط)
            'user_id'     => 'sometimes|required|integer|exists:users,id',

            // Images upload
            'images'   => 'nullable|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',

            'contacts'           => 'nullable|array',
            'contacts.*.type'    => 'required|string|in:phone,whatsapp,facebook,instagram,email',
            'contacts.*.value'   => 'required|string|max:255',

            'working_hours'               => 'required|array|size:7',
            'working_hours.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'working_hours.*.is_closed'   => 'required|boolean',
            'working_hours.*.open_time'   => 'required_if:working_hours.*.is_closed,false|nullable|date_format:H:i',
            'working_hours.*.close_time'  => 'required_if:working_hours.*.is_closed,false|nullable|date_format:H:i',
        ];
    }

    /**
     * Define body parameters for Scribe documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'name'        => ['description' => 'The name of the entity.', 'example' => 'Our Village Heritage Restaurant'],
            'category_id' => ['description' => 'The subcategory ID link.', 'example' => 2],
            'description' => ['description' => 'The description text.', 'example' => 'Serving authentic traditional countryside breakfast.'],
            'address'     => ['description' => 'The location address details.', 'example' => 'Yabroud, Al-Qaa Square'],
            'latitude'    => ['description' => 'The GPS latitude.', 'example' => 33.9600],
            'longitude'   => ['description' => 'The GPS longitude.', 'example' => 36.6500],
            'price_range' => ['description' => 'The price classification.', 'example' => 'Moderate'],
            'status'      => ['description' => 'The administrative status of the entity listing.', 'example' => 'pending'],
            'is_verified' => ['description' => 'Verification badge flag assigned by system moderators.', 'example' => false],
            'user_id'     => ['description' => 'Assign this entity to a specific user ID. [Admin Only feature]', 'example' => 14, 'required' => false],
            'images'      => ['description' => 'Image files for the active entity gallery.', 'required' => false, 'type' => 'file[]'],
            'contacts'    => [
                'description' => 'Array for the entity communication methods.',
                'example' => [
                    ['type' => 'phone', 'value' => '+963115555555'],
                    ['type' => 'email', 'value' => 'info@example.com']
                ]
            ],
            'contacts.*.type'             => ['description' => 'The communication channel type.', 'required' => true],
            'contacts.*.value'            => ['description' => 'The address/number for the channel.', 'required' => true],
            'working_hours'               => [
                'description' => 'Full 7-day schedule array.',
                'example' => [
                    ['day_of_week' => 0, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
                    ['day_of_week' => 1, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
                    ['day_of_week' => 2, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
                    ['day_of_week' => 3, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
                    ['day_of_week' => 4, 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
                    ['day_of_week' => 5, 'is_closed' => false, 'open_time' => '13:00', 'close_time' => '23:00'],
                    ['day_of_week' => 6, 'is_closed' => true, 'open_time' => null, 'close_time' => null],
                ]
            ],
            'working_hours.*.day_of_week' => ['description' => 'Day representation index (0-6).', 'required' => true],
            'working_hours.*.is_closed'   => ['description' => 'Status of business on this specific day.', 'required' => true],
            'working_hours.*.open_time'   => ['description' => 'Opening shift time (HH:mm format).', 'required' => false],
            'working_hours.*.close_time'  => ['description' => 'Closing shift time (HH:mm format).', 'required' => false],
        ];
    }
}
