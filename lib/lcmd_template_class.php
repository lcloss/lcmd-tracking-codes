<?php
namespace LCMD;

if ( !class_exists('LCMD\\Template') ) {
    class Template 
    {
        const TPL_DEFAULT_EXTENSION = '.php';
        const TPL_DEFAULT_TEMPLATE = 'index';
        const TPL_VIEWS_FOLDER = '/includes/views';
    
        // Store the location of template
        var $the_location = '';
        // Store the template name
        var $the_template = '';
        // Store the doc content
        var $the_doc = '';
        // Store if's
        var $ifs = [];
        // Store for's
        var $fors = [];
        // Store entries
        var $entries = [];
        // Store user messages
        var $message;
        // Check if is already on template folder
        var $on_template_folder = False;
    
        public function __construct( $template_file = '', $default_location = '', $data = [] ) 
        {
            if ($default_location != '') {
                $this->setLocation($default_location);
            } else {
                $this->setLocation(self::TPL_VIEWS_FOLDER);
            }
            if ($template_file != '') {
                $this->setTemplate($template_file);
            }
            if (!empty($data)) {
                $this->setData($data);
            }
        }
    
        // Method to output the template
        public function run( $return_value = false ) {
            $this->_parse();
    
            if ( $return_value == true ) {
                return $this->the_doc;
            } else {
                echo $this->the_doc;
            }
        }
    
        // Load the template
        private function _load() {
            $tpl = $this->the_location . $this->the_template . $this::TPL_DEFAULT_EXTENSION;
            if ( is_file($tpl) && is_readable($tpl) ) {
                $this->the_doc = file_get_contents($tpl);
            } else {
                $tpl = $this->the_location . $this::TPL_DEFAULT_TEMPLATE . $this::TPL_DEFAULT_EXTENSION;
                if ( is_file($tpl) && is_readable($tpl) ) {
                    $this->the_doc = file_get_contents($tpl);
                } else {
                    throw new \Exception('No template ' . $this->the_template . ' nor ' . $this::TPL_DEFAULT_TEMPLATE . ' was found. File ' . $tpl);
                }
            }
        }
    
        // Parse the template
        private function _parse() {
            // First extract for 
            $this->_extractFor();
            // ...than, replace all for entries
            $this->_replaceFor();
            // ...extract if
            $this->_extractIf();
            // ...than, replace all if entries
            $this->_replaceIf();
            // ...than, replace all entries
            $this->_replace();
            // ...than, extract blocks
            $this->_extractBlocks();
    
            // Check for 'use' a template
            $use_pattern = '/{% use (.*) %}/i';
            preg_match($use_pattern, $this->the_doc, $matches);
    
            if ( count($matches) > 0 ) {
                $parent_template = $matches[1];
                // Clear tag
                $this->the_doc = preg_replace($use_pattern, NULL, $this->the_doc);
                // Get the doc as content
                $content = $this->getTheDoc();
    
                if ( $this->on_template_folder ) {
                    $location = $this->getLocation();
                } else {
                    $location = $this->getLocation() . 'template/';
                    $this->on_template_folder = True;
                }
    
                $template = new Template($location, $parent_template);
                $template->setOnTemplate(True);
                $template->setData($this->getEntries());
                $template->setEntry('content', $content);
                // Update the doc
                $this->setTheDoc($template->run(true));
            }
        }
    
        private function _extractBlocks() {
            // Extract all block as entries
            $block_pattern = '/{% block (\w+) %}(.*?){% endblock %}/is';
            preg_match_all($block_pattern, $this->the_doc, $matches);
    
            if ( count($matches) > 0 ) {
                foreach ($matches[1] as $i => $entry) {
                    if ( count($matches[0]) > 0 ) {
                        // Set the appropriate entry
                        $this->setEntry($entry, $matches[2][$i]);
    
                        // Clear block content
                        $this->the_doc = str_replace($matches[0][$i], NULL, $this->the_doc);
                    }
                }
            }
        }
    
        private function _extractFor() {
            // Extract all for as entries
            $for_pattern = '/{% for ([\w\.]+) %}(.*?){% endfor %}/is';
            preg_match_all($for_pattern, $this->the_doc, $matches);
    
            // Run over all occurrences
            foreach($matches[0] as $k => $match) {
                // Set the content of this occurrence
                $this->setFor($matches[1][$k], $matches[2][$k]);
    
                // Replace block content by the key
                $this->the_doc = str_replace($match, '{{ ' . $matches[1][$k] . ' }}', $this->the_doc);
            }
        }
    
        private function _extractIf() {
            $if_pattern = '/{% if ([\w\.]+) %}(.*?)(?:{% else %}(.*?))?{% endif %}/is';
            preg_match_all($if_pattern, $this->the_doc, $matches);
    
            // Run over all occurrences
            foreach ($matches[0] as $k => $match) {
                // Check for the entry (first key)
                if (array_key_exists($matches[1][$k], $this->getEntries())) {
                    if ($this->getEntry($matches[1][$k])) {
                        // Replace by the true
                        $content = $matches[2][$k];
                    } else {
                        // Replace by the false or empty
                        $content = $matches[3][$k];
                    }
                    // Replace this occurrence with the appropriate content
                    $this->the_doc = str_replace($match, $content, $this->the_doc);
                } else {
                    $this->the_doc = str_replace($match, $matches[3][$k], $this->the_doc);
                }
            }
        }
    
        // Replace for entries
        private function _replaceFor() {
            foreach ($this->fors as $entry => $data) {
                $content = $this->getEntry($entry);
    
                if ( is_array($content) ) {
                    $block = '';
                    foreach($content as $data) {
                        $for_template = new Template();
                        $for_template->setData($this->getEntries());
    
                        if ( is_array($data) ) {
                            $for_template->setTheDoc($this->getFor($entry));
                            foreach($data as $key => $value) {
                                $for_template->setEntry($entry . '.' . $key, $value);
                            }
                        }
                        $block .= $for_template->run(true);
                    }
                    $this->setEntry($entry, $block);
                }
            }
        }
    
        // Replace if entries
        private function _replaceIf() {
            foreach ($this->ifs as $entry => $data) {
                $if_template = new Template();
                $if_template->setData($this->getEntries());
    
                if ( $this->getEntry($entry) ) {
                    $if_template->setEntry($entry, $data[0]);
                } else {
                    $if_template->setEntry($entry, $data[1]);
                }
                $this->setEntry($entry, $if_template->run(true));
            }
        }
    
        // Replace template tags with data
        private function _replace() {
            // Remove comments
            $block_comment_pattern = '#/\*.*?\*/#s';
            $this->the_doc = preg_replace($block_comment_pattern, NULL, $this->the_doc);
    
            $line_comment_pattern = '#(?<!:)//.*#';
            $this->the_doc = preg_replace($line_comment_pattern, NULL, $this->the_doc);
            
            // Replace template keys
            $entry_pattern = '/{{ ([\w\.]+) }}/';
            preg_match_all($entry_pattern, $this->the_doc, $matches);
            if ( count($matches) > 0 ) {
                foreach($matches[1] as $i => $entry) {
                    if ( array_key_exists($entry, $this->entries) ) {
                        $this->the_doc = str_replace($matches[0][$i], $this->entries[$entry], $this->the_doc);
                    } else {
                        $this->the_doc = str_replace($matches[0][$i], NULL, $this->the_doc);
                    }
                }
            }
        }
    
        // Set an entry
        public function setEntry($entry_name, $content) {
            $this->entries[$entry_name] = $content;
        }
    
        public function getEntry($entry_name) {
            if ( array_key_exists($entry_name, $this->entries) ) {
                return $this->entries[$entry_name];
            } else {
                return '';
            }
        }
    
    
        // Set an for
        public function setFor($for_name, $content) {
            $this->fors[$for_name] = $content;
        }
    
        public function getFor($for_name) {
            if ( array_key_exists($for_name, $this->fors) ) {
                return $this->fors[$for_name];
            } else {
                return '';
            }
        }
    
    
        // Set a if
        public function setIf($if_name, $content) {
            // echo 'Setting ' . $if_name . '<br>';
            $this->ifs[$if_name] = $content;
        }
    
        // Set an array of entries
        public function setData($entries) {
            foreach($entries as $entry => $content) {
                $this->setEntry($entry, $content);
            }
        }
    
        // Set doc
        public function setTheDoc($content) {
            $this->the_doc = $content;
        }
    
        public function getTheDoc() {
            return $this->the_doc;
        }
    
        public function setOnTemplate($on_template = False) {
            $this->on_template_folder = $on_template;
        }
    
        // Set template
        public function setTemplate($template_file) {
            $this->the_template = $template_file;
    
            if ( !empty($this->the_template) ) {
                $this->_load();
            }
        }
    
        public function getTemplate() {
            return $this->the_template;
        }
        
        // Set location
        public function setLocation($location) {
            $this->the_location = $location;
        }
    
        public function getLocation() {
            if ( empty($this->the_location) ) {
                return self::TPL_VIEWS_FOLDER;
            } else {
                return $this->the_location;
            }
        }
    
        // Get entries
        public function getEntries() {
            return $this->entries;
        }
    }
}

?>