<?php

namespace App\Rules;

use App\Models\Genre;
use Illuminate\Contracts\Validation\Rule;

class IsValidVideoCategory implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //TODO After - Could not implement by myself
        //Check all categories that belongs to genre
        /** @var Video $value */
        //$genres = $value->genres();
        //dd($genres);
        //$allowedCategories = [];
        //foreach ($genres as $genre) {
        //    $allowedCategories = array_merge($allowedCategories, $genre->categories());
        //}
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
