<?php declare(strict_types = 1);

/**
 * Messaging class that uses the session.
 */
final class Messages extends Session
{

    /**
     * Add an error message.
     *
     * @static
     *
     * @param string $message
     */
    public static function addError(string $message): void
    {
        self::add('errors', $message);
    }

    /**
     * Get list of messages from Session key "messages".
     *
     * @return array
     */
    public static function getMessages(): array
    {
        $messages = parent::getValue('messages');

        return $messages ?? [];
    }

    /**
     * Show error messages.
     *
     * @return array    List of error messages.
     */
    public static function flashErrors(): array
    {
        return self::flash('errors');
    }

    /**
     * Shows list of messages by given Session messages key.
     *
     * @param string $key   Session key in "messages". Currently supports "errors".
     *
     * @return array        Return list of messages or empty array if messages don't exist.
     */
    private static function flash(string $key): array
    {
        // Get list of all current messages in Session.
        $old_messages = self::getMessages();

        // Get list of specific message type and clear them.
        if (array_key_exists($key, $old_messages)) {
            $new_messages = $old_messages;
            $new_messages[$key] = [];

            parent::setValue('messages', $new_messages);
        }

        // Show list of messages that we cleared one time only.
        return array_key_exists($key, $old_messages) ? $old_messages[$key] : [];
    }

    /**
     * Checks if Session "messages" key also has "errors".
     * 
     * @return bool     Return true if error messages exists or false if error messages don't exist.
     */
    public static function hasErrors(): bool {
        return self::has('errors');
    }

    /**
     * Checks Session "messages" key for a specific type of message.
     * 
     * @param string $key   Session key in "messages".
     *
     * @return bool         Return true if messages exists or false if messages don't exist.
     */
    private static function has(string $key): bool {
        $messages = self::getMessages();

        return (array_key_exists($key, $messages) && $messages[$key]);
    }

    /**
     * Adds message to session. First checks if messages, exist. If they exist, append message by given type. If not,
     * create a new "messages" key in session and add the message.
     *
     * @static
     *
     * @param string $type      Message type. Currently supports "errors" type. Could add more in the future.
     * @param mixed  $message   Usually a string or an array.
     */
    private static function add(string $type, $message): void
    {
        // Get list of messages from Session "messages" key.
        $messages = self::getMessages();

        // Append message or create "messages" key.
        if ($messages) {
            $messages[$type][] = $message;
            parent::setValue('messages', $messages);
        }
        else {
            parent::setValue('messages', [
                $type => [$message]
            ]);
        }
    }
}
