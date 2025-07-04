<?php

class Template{
    protected $template_location;

    protected $vars;

    protected $mla_request;      // Encapsulates superglobals e.g. $SESSION, $REQUEST, etc (misspelled in this comment to keep searches clean)
    protected $di_dbase;

    public function __construct(\Config $config) {
        $this->template_location = "{$config->app_path}/templates";

        $this->vars = [];
    }

    public function setTemplate($template_file) {
        $this->template_location = $this->template_location."/".$template_file;
    }

    /**
     * Summary of set
     * @param string $name
     * @param mixed $value mixed so array of file names can be passed in /list/index.php
     * @return void
     */
    public function set(string $name, mixed $value, bool $use_hsc = false): void
    {
        if ($use_hsc) {
            $value = self::specialchars($value);
        }
        $this->vars[$name] = $value;
    }

    public function specialchars(&$value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $sub_value) {
                $value[$key] = self::specialchars($value[$key]);
            }
        } else if (is_string($value)) {
            $value = htmlspecialchars($value);
        }
        return $value;
    }


    public function echoToScreen(): void {
        echo $this->loadTemplate(); // Display the contents directly to the page
    }

    /**
     * Hand over the rendered template, mate.
     * I'm gonna give it to this guy over here.
     *
     * This function is used to return the rendered template as a string.
     * It is used to get the inner content of what will be sent to a base template.
     * @return bool|string
     */
    public function grabTheGoods(): string {
        return $this->loadTemplate();
    }

    /**
     * Renders the template and saves it to a static file.
     * Creates the directory if it doesn't exist.
     * @param string $file_path The absolute path where the file should be saved.
     * @return bool True on success, false on failure.
     */
    public function saveToFile(string $file_path): bool {
        $content = $this->loadTemplate();
        if ($content === false) {
            return false;
        }

        $directory = dirname($file_path);
        if (!is_dir($directory)) {
            // Attempt to create the directory recursively
            if (!mkdir($directory, 0755, true)) {
                // In a real app, you might want to log this error
                return false;
            }
        }

        // Save the content to the file
        return file_put_contents($file_path, $content) !== false;
    }

    protected function loadTemplate(): string {
        $charEncode = "UTF-8";
        extract($this->vars);          	// Extract the vars to local namespace

        ob_start();                    	// Start output buffering

        if(!isset($this->template_location)) {
            echo "No template file provided";
        }

        include($this->template_location);	// Include the file

        $ob_result = ob_get_clean();

        // if $ob_result is false, return an error message
        if (empty($ob_result)) {
            return "Error loading template: {$this->template_location}";
        }

        return $ob_result;
    }
}
