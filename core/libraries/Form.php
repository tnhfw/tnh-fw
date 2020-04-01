<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */

    class Form {

        /**
         * Generate the form opened tag
         * @param  string $path       the form action path
         * @param  array  $attributes the additional form attributes
         * @param  string $method     the form method like 'GET', 'POST'
         * @return string             the generated form html
         */
        public static function open($path = null, array $attributes = array(), $method = 'POST') {
            if ($path) {
                $path = get_instance()->url->appUrl($path);
            }
            $method = strtoupper($method);
            $str = null;
            $str .= '<form action = "' . $path . '" method = "' . $method . '"';
            if (!isset($attributes['accept-charset'])) {
                $attributes['accept-charset'] = get_config('charset', 'UTF-8');
            }
            $str .= attributes_to_string($attributes);
            $str .= '>';
            //if CSRF is enabled in the configuration
            if (get_config('csrf_enable', false) && $method == 'POST') {
                $csrfValue = get_instance()->security->generateCSRF();
                $csrfName = get_config('csrf_key', 'csrf_key');
                $str .= self::hidden($csrfName, $csrfValue);
            }
            return $str;
        }

        /**
         * Generate the form opened tag for multipart like to send a file
         * @see Form::open() for more details
         * @return string the generated multipart form html
         */
        public static function openMultipart($path = null, array $attributes = array(), $method = 'POST') {
            $attributes['enctype'] = 'multipart/form-data';
            return self::open($path, $attributes, $method);
        }

        /**
         * Generate the form close
         * @return string the form close html
         */
        public static function close() {
            return '</form>';
        }

        /**
         * Generate the form fieldset & legend
         * @param  string $legend the legend tag value
         * @param  array  $fieldsetAttributes the fieldset additional HTML attributes
         * @param  array  $legendAttributes the legend additional HTML attributes. Is used only is $legend is not empty
         * @return string         the generated fieldset value
         */
        public static function fieldset($legend = '', array $fieldsetAttributes = array(), array $legendAttributes = array()) {
            $str = '<fieldset' . attributes_to_string($fieldsetAttributes) . '>';
            if ($legend) {
                $str .= '<legend' . attributes_to_string($legendAttributes) . '>' . $legend . '</legend>';
            }
            return $str;
        }

        /**
         * Generate the fieldset close tag
         * @return string the generated html for fieldset close
         */
        public static function fieldsetClose() {
            return '</fieldset>';
        }

        /**
         * Get the error message for the given form field name.
         * This use the form validation information to get the error information.
         * @param  string $name the form field name
         * @return string       the error message if exists and null if not
         */
        public static function error($name) {
            $return = null;
            $obj = & get_instance();
            if (isset($obj->formvalidation)) {
                $errors = $obj->formvalidation->getErrors();
                if (isset($errors[$name])) {
                    $return = $errors[$name];
                }
            }
            return $return;
        }

        /**
         * Get the form field value
         * @param  string $name    the form field name
         * @param  mixed $default the default value if can not found the given form field name
         * @return mixed the form field value if is set, otherwise return the default value.
         */
        public static function value($name, $default = null) {
            $value = get_instance()->request->query($name);
            if (is_string($value) && strlen($value) > 0) {
                return $value;
            }
            return $default;
        }

        /**
         * Generate the form label html content
         * @param  string $label      the title of the label
         * @param  string $for        the value of the label "for" attribute
         * @param  array  $attributes the additional attributes to be added
         * @return string the generated label html content
         */
        public static function label($label, $for = '', array $attributes = array()) {
            $str = '<label';
            if ($for) {
                $attributes['for'] = $for;
            }
            $str .= attributes_to_string($attributes);
            $str .= '>';
            $str .= $label . '</label>';
            return $str;
        }

        /**
         * Generate the form field for input like "text", "email", "password", etc.
         * @param  string $name       the form field name
         * @param  mixed $value      the form field value to be set
         * @param  array  $attributes the additional attributes to be added in the form input
         * @param  string $type       the type of the form field (password, text, submit, button, etc.)
         * @return string             the generated form field html content for the input
         */
        public static function input($name, $value = null, array $attributes = array(), $type = 'text') {
            $str = null;
            $str .= '<input';
            $attributes['type'] = $type;
            $attributes['name'] = $name;
            $attributes['value'] = $value;
            $str .= attributes_to_string($attributes);
            $str .= '/>';
            return $str;
        }
        
        /**
         * Generate the form field for "text"
         * @see Form::input() for more details
         */
        public static function text($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'text');
        }

        /**
         * Generate the form field for "password"
         * @see Form::input() for more details
         */
        public static function password($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'password');
        }

        /**
         * Generate the form field for "radio"
         * @see Form::input() for more details
         */
        public static function radio($name, $value = null, $checked = false, array $attributes = array()) {
            if ($checked) {
                $attributes['checked'] = true;
            }
            return self::input($name, $value, $attributes, 'radio');
        }

        /**
         * Generate the form field for "checkbox"
         * @see Form::input() for more details
         */
        public static function checkbox($name, $value = null, $checked = false, array $attributes = array()) {
            if ($checked) {
                $attributes['checked'] = true;
            }
            return self::input($name, $value, $attributes, 'checkbox');
        }

        /**
         * Generate the form field for "number"
         * @see Form::input() for more details
         */
        public static function number($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'number');
        }

        /**
         * Generate the form field for "phone"
         * @see Form::input() for more details
         */
        public static function phone($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'phone');
        }

        /**
         * Generate the form field for "email"
         * @see Form::input() for more details
         */
        public static function email($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'email');
        }
        
        /**
         * Generate the form field for "search"
         * @see Form::input() for more details
         */
        public static function search($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'search');
        }
        
        /**
         * Generate the form field for "hidden"
         * @see Form::input() for more details
         */
        public static function hidden($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'hidden');
        }
        
        /**
         * Generate the form field for "file"
         * @see Form::input() for more details
         */
        public static function file($name, array $attributes = array()) {
            return self::input($name, null, $attributes, 'file');
        }
        
        /**
         * Generate the form field for "button"
         * @see Form::input() for more details
         */
        public static function button($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'button');
        }
        
        /**
         * Generate the form field for "reset"
         * @see Form::input() for more details
         */
        public static function reset($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'reset');
        }
        
        /**
         * Generate the form field for "submit"
         * @see Form::input() for more details
         */
        public static function submit($name, $value = null, array $attributes = array()) {
            return self::input($name, $value, $attributes, 'submit');
        }

        /**
         * Generate the form field for textarea
         * @param  string $name       the name of the textarea field
         * @param  string $value      the textarea field value
         * @param  array  $attributes the additional attributes to be added
         * @return string             the generated textarea form html content
         */
        public static function textarea($name, $value = '', array $attributes = array()) {
            $str = null;
            $str .= '<textarea name = "' . $name . '"';
            $str .= attributes_to_string($attributes);
            $str .= '>';
            $str .= $value . '</textarea>';
            return $str;
        }
        
        /**
         * Generate the form field for select
         * @param  string $name       the name of the form field
         * @param  array $values      the values used to populate the "option" tags
         * @param  mixed $selected   the selected value in the option list
         * @param  array  $attributes the additional attribute to be added
         * @return string             the generated form field html content for select
         */
        public static function select($name, array $values = array(), $selected = null, array $attributes = array()) {
            $str = null;
            $str .= '<select name = "' . $name . '"';
            $str .= attributes_to_string($attributes);
            $str .= '>';
            foreach ($values as $key => $val) {
                $select = '';
                if ($key == $selected) {
                    $select = ' selected';
                }
                $str .= '<option value = "' . $key . '"' . $select . '>' . $val . '</option>';
            }
            $str .= '</select>';
            return $str;
        }

    }
