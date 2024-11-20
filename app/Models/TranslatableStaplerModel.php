<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Neko\Stapler\ORM\EloquentTrait;
use Neko\Stapler\ORM\StaplerableInterface;

/**
 * Abstract model implementing stapler and translatable interfaces
 *
 * @package App\Models
 */
abstract class TranslatableStaplerModel extends Model implements TranslatableContract, StaplerableInterface
{
    use Translatable, EloquentTrait {
        Translatable::getAttribute as getAttributeTranslatable;
        Translatable::setAttribute as setAttributeTranslatable;

        EloquentTrait::getAttribute as getAttributeStapler;
        EloquentTrait::setAttribute as setAttributeStapler;
    }

    /**
     * @var array
     */
    public $translatedAttributes = [];

    /**
     * Trait collision override for get Attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return $this->getAttributeStapler($key); //process by Stapler
        } elseif (in_array($key, $this->translatedAttributes)) {
            return $this->getAttributeTranslatable($key); //process by Translatable
        }

        return parent::getAttribute($key);
    }

    /**
     * Trait collision override for set Attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            if (empty($value)) {
                $value = STAPLER_NULL;
            } /**
             * For stapler we should pass the old path of the file as a string or null if empty.
             * Old images are wiped out. We need to check if the image is the same as previous and skip.
             */
            elseif ($this->attachedFiles['image']->url() === $value) {
                return;
            }
            // TODO: work weird, no idea, clean up later if appear to
            //             else {
            //                 $tmpFilePath = sys_get_temp_dir().'/'.basename($value);
            //                 copy(public_path('images/'.$value), $tmpFilePath);
            //                 $value = $tmpFilePath;
            //             }
            $this->setAttributeStapler($key, $value); //process by Stapler
            return;
        } elseif (in_array($key, $this->translatedAttributes)) {
            $this->setAttributeTranslatable($key, $value); //process by Translatable
            return;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * IMPORTANT: DO NOT REMOVE THIS!!!! trait EloquentTrait has getAttributes method!!! admin area will be broken!!!!
     * Get all of the current attributes on the model.
     *
     * Allows to correctly obtain attributes for STAPLER package as it collects
     *
     * @see https://github.com/CodeSleeve/laravel-stapler/issues/64#issuecomment-338445440
     * @note Must not be removed.
     * @return array
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }
}
