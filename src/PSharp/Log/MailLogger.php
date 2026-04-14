<?php
namespace PSharp\Log;

use Stringable;
use DateTime;
use PSharp\Support\Str;

/**
 * Logger that writes an email message about a log.
 * Should be reserved for very very relevant alerts!
 */
class MailLogger extends Logger
{
    protected $from = null;
    protected $to = null;
    protected $cc = [];
    protected $bcc = [];
    protected $subject = '[{level}] [{datetime}] An error occurred right now!';
    protected $body = "{hr}\nError Report\n{hr}\nTime: {datetime}\nLevel: {level}\nDescription: {error}\n{hr}\n{context}\n{hr}\n";


    /**
     * Creates this logger.
     * 
     * @param string $name
     * @param array|null $settings
     */
    public function __construct(string $name, array $settings = null)
    {
        $this->setName($name);

        $this->configure($settings ?? []);
    }

    /**
     * Configure some logger settings.
     * 
     * @param array $conf
     * @return void
     */
    protected function configure(array $conf)
    {
        $fields = ['from','to','cc','bcc','subject','body'];

        foreach ($fields as $field) {
            if (! empty($conf[$field])) {
                $this->$field = $conf[$field]; 
            }
        }

        if (! empty($conf['datetime'])) {
            $this->setDateLabelFormat($conf['datetime']); 
        }
    }

    /**
     * Writes to the log file.
     * 
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    protected function write($level, string|Stringable $message, array $context = array())
    {
        $now = $this->formatDate(new DateTime());

        $headers = [
            'From: '.$this->from,
            'Reply-To: '.$this->from,
            'To: '.$this->to,
        ];

        foreach ($this->cc as $mail) {
            $headers[] = 'Cc: '.$mail;
        }

        foreach ($this->bcc as $mail) {
            $headers[] = 'Bcc: '.$mail;
        }

        $variables = [
            'datetime' => $now,
            'error' => $message,
            'level' => $level,
            'hr' => str_repeat('-', 72),
        ];

        $subject = Str::replaceVariables($this->subject, $variables);

        $variables['context'] = json_encode($context, JSON_PRETTY_PRINT);

        $body = Str::replaceVariables($this->body, $variables);

        mail($this->to, $subject, $body, implode("\r\n", $headers));
    }
}