<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SurveyQuestion
 *
 * @property-read string|int $id
 * @property-read string $key_code
 * @property-read string $type
 * @property-read null|array $attributes
 * @property-read int $required
 * @property-read int $order
 * @property-read int $active
 * @property-read null|array $options
 *
 * @package App\Http\Resources
 */
final class SurveyQuestion extends JsonResource
{
    /**
     * Attributes that should not be translated.
     *
     * @var string[]
     */
    private array $untranslatableAttributes = [
        'age',
        'fat_percentage',
        'intensive_sports',
        'moderate_sports',
        'light_sports',
        'know_us'
    ];

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //TODO: correct translation keys must be implemented
        return [
            'id'          => $this->id,
            'key_code'    => $this->key_code,
            'label'       => trans("survey_questions.$this->key_code"),
            'description' => trans_fb("survey_questions.$this->key_code.description"), //TODO: find descriptions for fields
            'type'        => $this->type,
            'options'     => $this->reformat($this->translateOptions()),
            'attributes'  => $this->attributes,
            'required'    => $this->required,
            'order'       => $this->order,
            'active'      => $this->active,
        ];
    }

    /**
     * Retrieve translated option values.
     *
     * @return array|null
     */
    private function translateOptions(): ?array
    {
        if (is_null($this->options) || in_array($this->key_code, $this->untranslatableAttributes, true)) {
            return $this->options;
        }

        $updatedOptions = [];

        foreach ($this->options as $key => $value) {
            if ('daily_routine' === $this->key_code) {
                $updatedOptions[$value] = 'no_matter' === $value ?
                    trans('survey_questions.daily_routine_no_matter') :
                    trans("survey_questions.$value");
                continue;
            }

            $updatedOptions[$key] = 'no_matter' === $key ?
                trans("survey_questions.{$this->key_code}_$key") :
                trans("survey_questions.$key");
        }

        return $updatedOptions;
    }

    /**
     * Options should have structure slightly different from what is stored in the DB.
     *
     * Extra tooltips are also added during this phase.
     *
     * @param array|null $options
     *
     * @return array|null
     */
    private function reformat(?array $options): ?array
    {
        if (is_null($options)) {
            return null;
        }

        $updatedOptions = [];

        foreach ($options as $key => $option) {
            // TODO: upon refactor all translations for keys should come to single standard to avoid extra checks
            $dictionaryKey = in_array(
                $this->key_code,
                ['intensive_sports', 'moderate_sports', 'light_sports', 'fat_percentage']
            ) ?
                "{$this->key_code}_tooltip" :
                ('daily_routine' === $this->key_code ? "{$key}_tooltip" : "{$this->key_code}_{$key}_tooltip");

            if (is_array($option) && key_exists('options', $option) && is_array($option['options'])) {
                $option['options'] = $this->reformat($option['options']);
            }

            $updatedOptions[] = [
                'key'     => $key,
                'value'   => $option,
                'tooltip' => trans_fb("survey_questions.$dictionaryKey")
            ];
        }

        return $updatedOptions;
    }
}
