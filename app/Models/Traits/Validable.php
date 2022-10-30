<?php namespace MotionArray\Models\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\MessageBag;
use Hash;
use Validator;

Trait Validable
{
    /**
     * Validation is executed on save. In the event that a relationship
     * needs to be validated along with the model, e.g. a Resource
     * should have a Category but the relationship itself is stored in
     * a pivot table and not in the resources table. This array can be
     * used to prevent saving of specific attributes which would
     * otherwise result in a database error.
     */
    public static $excludeAttributes = [];

    public static $rules = [];
    public static $messages = [];
    public $errors = [];

    protected static $validationEnabled = true;

    static public function withoutValidation($callback)
    {
        static::$validationEnabled = false;
        try {
            $callback();
        } finally {
            static::$validationEnabled = true;
        }
    }

    /**
     * Validate model attributes
     * @return boolean
     */
    public function validate()
    {
        if (!static::$validationEnabled) {
            return true;
        }

        // If $updateRules are present on use them for validation if
        // this model is an existing entry in the database
        $rules = static::$rules;

        // Replace {:id} on rules (for unique validation)
        if ($this->id) {
            foreach ($rules as &$rule) {
                $rule = str_replace('{:id}', $this->id, $rule);
            }
        }

        // Decide on which rules to use. Assign any model id's declared in rules.
        if (isset(static::$updateRules)) {
            $rules = $this->exists ? static::$updateRules : static::$rules;
            $replace = ($this->getKey() > 0) ? $this->getKey() : '';

            foreach ($rules as $key => $rule) {
                $rules[$key] = str_replace('{:id}', $replace, $rule);
            }
        }

        //Run through input validation
        $v = Validator::make($this->attributes, $rules, static::$messages);

        $valid = $v->passes();

        if ($valid) {
            // Remove any attributes that shouldn't be save to the
            // models table
            foreach (static::$excludeAttributes as $attribute) {
                unset($this->attributes[$attribute]);
            }
        } else {
            $this->errors = $v->messages();
        }

        $this->fireModelEvent('validated');

        return $valid;
    }

    public function failed()
    {
        return !!count($this->errors);
    }

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function validating($callback)
    {
        static::registerModelEvent('validating', $callback);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function validated($callback)
    {
        static::registerModelEvent('validated', $callback);
    }
}
