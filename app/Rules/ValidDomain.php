<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDomain implements ValidationRule
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // validate             (1.0) Run the validation rule.

     /**
     * <Layer number> (1.0)
     *
     * Processing name: validate
     * Function: Run the validation rule.
     *
     * @param \Closure(string, ?string=)  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parsedUrl = parse_url($value);

        if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
            // Reconstruct the URL with only scheme and host
            $domainOnlyUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            // Check if the reconstructed URL matches the input value
            if ($value !== $domainOnlyUrl) {
                $fail('The :attribute must be a valid URL containing only the domain.');
            }
        }
    }
}
