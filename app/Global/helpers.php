<?php

use Pusher\Pusher;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| This is where you can define various globally re-usable helpers.
|
*/

/**************************************************************************/
/* Processing Hierarchy                                                   */
/**************************************************************************/
// get_message          (1.0) Get message from message list using the message
//                              id and its category.
// get_action           (2.0) Get action from message list using the message
//                              id and its category.
// make_class_instance  (3.0) Creates a new instance of a class using reflection.
// hex_to_base64        (4.0) Converts a hexadecimal string to a base64 encoded
//                             string.
// formatEndian         (5.0) Convert endian hex string to specified format
// is_channel_occupied  (6.0) Check if pusher channel is currently occupied.
// unslug               (7.0) Convert a slug to a human-readable format.
// slug                 (8.0) Convert a string to slug.


/**
 * <Layer number> (1.0)
 *
 * <Processing name> get_message
 * <Function> Get message from message list using the message id and in
 *            its category.
 *
 * @param  string      $category
 * @param  string      $key
 * @param  array       $replace
 * @return string|null
 */
if (!function_exists('get_message')) {
    function get_message(String $category, String $key, $replace = []): ?String
    {
        return in_array($key, array_keys(__("messages.{$category}"))) ?
            __("messages.{$category}.{$key}.message", $replace)
            : null;
    }
}

/**
 * <Layer number> (2.0)
 *
 * <Processing name> get_action
 * <Function> Retrieves a translated action from the message list, based on the
 *            provided category and key.
 *
 * @param string $category
 * @param string $key
 * @param array $replace
 *
 * @return string|null
 */
if (!function_exists('get_action')) {
    function get_action(String $category, String $key, $replace = []): ?String
    {
        return in_array($key, array_keys(__("messages.{$category}"))) ?
            __("messages.{$category}.{$key}.action", $replace)
            : null;
    }
}

/**
 * <Layer number> (3.0)
 *
 * <Processing name> make_class_instance
 * <Function> Creates a new instance of a class using reflection.
 *
 * @param string $class
 * @param array ...$args
 *
 * @return object
 */
if (!function_exists('make_class_instance')) {
    function make_class_instance(String $class, ...$args): object
    {
        // Create reflection class of called class
        $reflection = new ReflectionClass($class);

        // Check if construct method exists
        if (!method_exists($class, '__construct')) {
            return $reflection->newInstanceWithoutConstructor();
        }

        // Return new instance with arguments
        return $reflection->newInstanceArgs($args);
    }
}

/**
 * <Layer number> (4.0)
 *
 * <Processing name> hex_to_base64
 * <Function> Converts a hexadecimal string to a base64 encoded string.
 *
 * @param string $hex

 * @return string
 */
if (!function_exists('hex_to_base64')) {
    function hex_to_base64(string $hex): string
    {
        return base64_encode(hex2bin($hex));
    }
}

/**
 * <Layer number> (5.0)
 *
 * <Processing name> formatEndian
 * <Function> Convert endian hex string to specified format.
 *
 * @param string $endian Endian HEX string
 * @param string $format Endian format: 'N' - big endian, 'V' - little endian
 *
 * @return string
 */
if (!function_exists('formatEndian')) {
    function formatEndian($endian, $format = 'N')
    {
        $endian = intval($endian, 16);      // convert string to hex
        $endian = pack('L', $endian);       // pack hex to binary sting (unsinged long, machine byte order)
        $endian = unpack($format, $endian); // convert binary sting to specified endian format

        return sprintf("%'.08x", $endian[1]); // return endian as a hex string (with padding zero)
    }
}

/**
 * <Layer number> (6.0)
 *
 * <Processing name> is_channel_occupied
 * <Function> Check if pusher channel is currently occupied.
 *
 * @param string $channel
 * @return boolean
 */
if (!function_exists('is_channel_occupied')) {
    function is_channel_occupied(string $channel): mixed
    {
        $isOccupied = false;
        // Create a new Pusher instance

        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );

        // Check if the channel occupation is already cached in the request cycle
        if (app()->has("pusher_channel_occupied_{$channel}")) {
            $isOccupied = app("pusher_channel_occupied_{$channel}");
        } else {
            $isOccupied = $pusher->getChannelInfo($channel)->occupied;
            app()->instance("pusher_channel_occupied_{$channel}", $isOccupied);
        }

        return $isOccupied;
    }
}

/**
 * <Layer number> (7.0)
 *
 * <Processing name> unslug
 * <Function> Convert a slug to a human-readable format.
 *
 * @param string $slug
 * @return string
 */
if (!function_exists('unslug')) {
    function unslug(string $slug): string
    {
        // Decode URL encoded apostrophes
        $decoded = str_replace('%27', "'", $slug);
        return Str::title(str_replace('-', ' ', $decoded));
    }
}

/**
 * <Layer number> (8.0)
 *
 * <Processing name> slug
 * <Function> Convert a string to slug.
 *
 * @param string $slug
 * @return string
 */
if (!function_exists('slug')) {
    function slug(string $string): string
    {
        $slug = strtolower(trim($string));
        // URL encode apostrophes
        $slug = str_replace("'", '%27', $slug);
        // Replace spaces with hyphens
        $slug = preg_replace('/\s+/', '-', $slug);
        // Remove other special characters except encoded apostrophes
        $slug = preg_replace('/[^a-z0-9%\-]/', '-', $slug);
        // Clean up multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        // Trim hyphens from start/end
        return trim($slug, '-');
    }
}
