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
    
    /**
     * Simple Mail
     *
     * A simple PHP wrapper class for sending email using the mail() method.
     *
     * PHP version > 5.2
     *
     * LICENSE: This source file is subject to the MIT license, which is
     * available through the world-wide-web at the following URI:
     * http://github.com/eoghanobrien/php-simple-mail/LICENCE.txt
     *
     * @category  SimpleMail
     * @package   SimpleMail
     * @author    Eoghan O'Brien <eoghan@eoghanobrien.com>
     * @copyright 2009 - 2017 Eoghan O'Brien
     * @license   http://github.com/eoghanobrien/php-simple-mail/LICENCE.txt MIT
     * @version   1.7.1
     * @link      http://github.com/eoghanobrien/php-simple-mail
     */

    class Email extends BaseClass{
        /**
         * @var int $wrap
         */
        protected $wrap = 78;

        /**
         * @var array $to
         */
        protected $to = array();

        /**
         * @var string $subject
         */
        protected $subject;

        /**
         * @var string $message
         */
        protected $message;

        /**
         * @var array $headers
         */
        protected $headers = array();

        /**
         * @var string $parameters
         */
        protected $params;

        /**
         * @var array $attachments
         */
        protected $attachments = array();

        /**
         * @var string $uid
         */
        protected $uid;
		

        /**
         * __construct
         *
         * Resets the class properties.
         */
        public function __construct() {
            parent::__construct();
            $this->reset();
        }

        /**
         * reset
         *
         * Resets all properties to initial state.
         *
         * @return object
         */
        public function reset() {
            $this->to = array();
            $this->headers = array();
            $this->subject = null;
            $this->message = null;
            $this->wrap = 78;
            $this->params = null;
            $this->attachments = array();
            $this->uid = $this->getUniqueId();
            return $this;
        }
		
            /**
             * setFrom
             *
             * @param string $email The email to send as from.
             * @param string $name  The name to send as from.
             *
             * @return object
             */
        public function setFrom($email, $name = null) {
            $this->addMailHeader('From', (string) $email, (string) $name);
            return $this;
        }

        /**
         * setTo
         *
         * @param string $email The email address to send to.
         * @param string $name  The name of the person to send to.
         *
         * @return object
         */
        public function setTo($email, $name = null) {
            $this->to[] = $this->formatHeader((string) $email, (string) $name);
            return $this;
        }
		
        /**
         * Set destination using array
         * @params array $emails the list of recipient. This is an associative array name => email
         * @example array('John Doe' => 'email1@example.com')
         * 
         * @return object the current instance
         */
        public function setTos(array $emails) {
            foreach ($emails as $name => $email) {
                if (is_numeric($name)) {
                    $this->setTo($email);
                } else {
                    $this->setTo($email, $name);
                }
            }
            return $this;
        }


        /**
         * getTo
         *
         * Return an array of formatted To addresses.
         *
         * @return array
         */
        public function getTo() {
            return $this->to;
        }


        /**
         * setCc
         * 
         * @param array  $pairs  An array of name => email pairs.
         * @example array('John Doe' => 'email1@example.com')
         * @return object
         */
        public function setCc(array $pairs) {
            return $this->addMailHeaders('Cc', $pairs);
        }

        /**
         * setBcc
         * 
         * @param array  $pairs  An array of name => email pairs.
         * @example array('John Doe' => 'email1@example.com')
         *
         * @return object
         */
        public function setBcc(array $pairs) {
            return $this->addMailHeaders('Bcc', $pairs);
        }

        /**
         * setReplyTo
         *
         * @param string $email
         * @param string $name
         *
         * @return object
         */
        public function setReplyTo($email, $name = null) {
            return $this->addMailHeader('Reply-To', $email, $name);
        }

        /**
         * setHtml
         *
         * @return object
         */
        public function setHtml() {
            $this->addGenericHeader(
                'Content-Type', 'text/html; charset="utf-8"'
            );
            return $this;
        }

        /**
         * setSubject
         *
         * @param string $subject The email subject
         *
         * @return object
         */
        public function setSubject($subject) {
            $this->subject = $this->encodeUtf8(
                $this->filterOther((string) $subject)
            );
            return $this;
        }

        /**
         * getSubject function.
         *
         * @return string
         */
        public function getSubject() {
            return $this->subject;
        }

        /**
         * setMessage
         *
         * @param string $message The message to send.
         *
         * @return object
         */
        public function setMessage($message) {
            $this->message = str_replace("\n.", "\n..", (string) $message);
            return $this;
        }

        /**
         * getMessage
         *
         * @return string
         */
        public function getMessage() {
            return $this->message;
        }

        /**
         * addAttachment
         *
         * @param string $path The file path to the attachment.
         * @param string $filename The filename of the attachment when emailed.
         * @param string $data
         * 
         * @return object
         */
        public function addAttachment($path, $filename = null, $data = null) {
            if (!file_exists($path)) {
                show_error('The file [' . $path . '] does not exists.');
                return $this;
            }
            if (empty($filename)) {
                $filename = basename($path);
            }
            $filename = $this->encodeUtf8($this->filterOther((string) $filename));
            if (empty($data)) {
               $data = $this->getAttachmentData($path);
            }
            $this->attachments[] = array(
                'path' => $path,
                'file' => $filename,
                'data' => chunk_split(base64_encode($data))
            );
            return $this;
        }

        /**
         * getAttachmentData
         *
         * @param string $path The path to the attachment file.
         *
         * @return string|boolean
         */
        public function getAttachmentData($path) {
            if (!file_exists($path)) {
                show_error('The file [' . $path . '] does not exists.');
                return false;
            }
            $filesize = filesize($path);
            $handle = fopen($path, "r");
            $attachment = null;
            if (is_resource($handle)) {
                $attachment = fread($handle, $filesize);
                fclose($handle);
            }
            return $attachment;
        }

        /**
         * addMailHeader
         *
         * @param string $header The header to add.
         * @param string $email  The email to add.
         * @param string $name   The name to add.
         *
         * @return object
         */
        public function addMailHeader($header, $email, $name = null) {
            $address = $this->formatHeader((string) $email, (string) $name);
            $this->headers[] = sprintf('%s: %s', (string) $header, $address);
            return $this;
        }

        /**
         * addMailHeaders
         *
         * @param string $header The header to add.
         * @param array  $pairs  An array of name => email pairs.
         *
         * @return object
         */
        public function addMailHeaders($header, array $pairs) {
            if (count($pairs) === 0) {
                show_error('You must pass at least one name => email pair.');
                return $this;
            }
            $addresses = array();
            foreach ($pairs as $name => $email) {
                if (is_numeric($name)) {
                   $name = null;
                }
                $addresses[] = $this->formatHeader($email, $name);
            }
            $this->addGenericHeader($header, implode(',', $addresses));
            return $this;
        }

        /**
         * addGenericHeader
         *
         * @param string $name The generic header to add.
         * @param mixed  $value  The value of the header.
         *
         * @return object
         */
        public function addGenericHeader($name, $value) {
            $this->headers[] = sprintf(
                '%s: %s',
                (string) $name,
                (string) $value
            );
            return $this;
        }

        /**
         * getHeaders
         *
         * Return the headers registered so far as an array.
         *
         * @return array
         */
        public function getHeaders() {
            return $this->headers;
        }

        /**
         * setAdditionalParameters
         *
         * Such as "-fyouremail@yourserver.com
         *
         * @param string $additionalParameters The addition mail parameter.
         *
         * @return object
         */
        public function setParameters($additionalParameters) {
            $this->params = (string) $additionalParameters;
            return $this;
        }

        /**
         * getAdditionalParameters
         *
         * @return string
         */
        public function getParameters() {
            return $this->params;
        }

        /**
         * setWrap
         *
         * @param int $wrap The number of characters at which the message will wrap.
         *
         * @return object
         */
        public function setWrap($wrap = 78) {
            $wrap = (int) $wrap;
            if ($wrap < 1) {
                $wrap = 78;
            }
            $this->wrap = $wrap;
            return $this;
        }

        /**
         * getWrap
         *
         * @return int
         */
        public function getWrap() {
            return $this->wrap;
        }

        /**
         * hasAttachments
         * 
         * Checks if the email has any registered attachments.
         *
         * @return bool
         */
        public function hasAttachments() {
            return !empty($this->attachments);
        }

        /**
         * assembleAttachment
         *
         * @return string
         */
        public function assembleAttachmentHeaders() {
            $head = array();
            $head[] = "MIME-Version: 1.0";
            $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->uid}\"";

            return join(PHP_EOL, $head);
        }

        /**
         * assembleAttachmentBody
         *
         * @return string
         */
        public function assembleAttachmentBody() {
            $body = array();
            $body[] = "This is a multi-part message in MIME format.";
            $body[] = "--{$this->uid}";
            $body[] = "Content-Type: text/html; charset=\"utf-8\"";
            $body[] = "Content-Transfer-Encoding: quoted-printable";
            $body[] = "";
            $body[] = quoted_printable_encode($this->message);
            $body[] = "";
            $body[] = "--{$this->uid}";

            foreach ($this->attachments as $attachment) {
                $body[] = $this->getAttachmentMimeTemplate($attachment);
            }

            return implode(PHP_EOL, $body) . '--';
        }

        /**
         * getAttachmentMimeTemplate
         *
         * @param array  $attachment An array containing 'file' and 'data' keys.
         *
         * @return string
         */
        public function getAttachmentMimeTemplate($attachment) {
            $file = $attachment['file'];
            $data = $attachment['data'];

            $head = array();
            $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
            $head[] = "Content-Transfer-Encoding: base64";
            $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
            $head[] = "";
            $head[] = $data;
            $head[] = "";
            $head[] = "--{$this->uid}";

            return implode(PHP_EOL, $head);
        }

        /**
         * send the email
         *
         * @return boolean
         */
        public function send() {
            $to = $this->getToForSend();
            $headers = $this->getHeadersForSend();

            if (empty($to)) {
                show_error('Unable to send, no To address has been set.');
            }

            if ($this->hasAttachments()) {
                $message  = $this->assembleAttachmentBody();
                $headers .= PHP_EOL . $this->assembleAttachmentHeaders();
            } else {
                $message = $this->getWrapMessage();
            }
            $this->logger->info('Sending new mail, the information are listed below: destination: ' . $to . ', headers: ' . $headers . ', message: ' . $message);
            return mail($to, $this->subject, $message, $headers, $this->params);
        }

        /**
         * Debug
         * @codeCoverageIgnore
         * 
         * @return string
         */
        public function debug() {
            return '<pre>' . print_r($this, true) . '</pre>';
        }

        /**
         * magic __toString function
         * @codeCoverageIgnore
         * 
         * @return string
         */
        public function __toString() {
            return print_r($this, true);
        }

        /**
         * formatHeader
         *
         * Formats a display address for emails according to RFC2822 e.g.
         * Name <address@domain.tld>
         *
         * @param string $email The email address.
         * @param string $name  The display name.
         *
         * @return string
         */
        public function formatHeader($email, $name = null) {
            $email = $this->filterEmail((string) $email);
            if (empty($name)) {
                return $email;
            }
            $name = $this->encodeUtf8($this->filterName((string) $name));
            return sprintf('"%s" <%s>', $name, $email);
        }

        /**
         * encodeUtf8
         *
         * @param string $value The value to encode.
         *
         * @return string
         */
        public function encodeUtf8($value) {
            $value = trim($value);
            if (preg_match('/(\s)/', $value)) {
                return $this->encodeUtf8Words($value);
            }
            return $this->encodeUtf8Word($value);
        }

        /**
         * encodeUtf8Word
         *
         * @param string $value The word to encode.
         *
         * @return string
         */
        public function encodeUtf8Word($value) {
            return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
        }

        /**
         * encodeUtf8Words
         *
         * @param string $value The words to encode.
         *
         * @return string
         */
        public function encodeUtf8Words($value) {
            $words = explode(' ', $value);
            $encoded = array();
            foreach ($words as $word) {
                $encoded[] = $this->encodeUtf8Word($word);
            }
            return join($this->encodeUtf8Word(' '), $encoded);
        }

        /**
         * filterEmail
         *
         * Removes any carriage return, line feed, tab, double quote, comma
         * and angle bracket characters before sanitizing the email address.
         *
         * @param string $email The email to filter.
         *
         * @return string
         */
        public function filterEmail($email) {
            $rule = array(
                "\r" => '',
                "\n" => '',
                "\t" => '',
                '"'  => '',
                ','  => '',
                '<'  => '',
                '>'  => ''
            );
            $email = strtr($email, $rule);
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            return $email;
        }

        /**
         * filterName
         *
         * Removes any carriage return, line feed or tab characters. Replaces
         * double quotes with single quotes and angle brackets with square
         * brackets, before sanitizing the string and stripping out html tags.
         *
         * @param string $name The name to filter.
         *
         * @return string
         */
        public function filterName($name) {
            $rule = array(
                "\r" => '',
                "\n" => '',
                "\t" => '',
                '"'  => "'",
                '<'  => '[',
                '>'  => ']',
            );
            $filtered = filter_var(
                $name,
                FILTER_SANITIZE_STRING,
                FILTER_FLAG_NO_ENCODE_QUOTES
            );
            return trim(strtr($filtered, $rule));
        }

        /**
         * filterOther
         *
         * Removes ASCII control characters including any carriage return, line
         * feed or tab characters.
         *
         * @param string $data The data to filter.
         *
         * @return string
         */
        public function filterOther($data) {
            return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
        }

        /**
         * getHeadersForSend
         *
         * @return string
         */
        public function getHeadersForSend() {
            if (empty($this->headers)) {
                return '';
            }
            return join(PHP_EOL, $this->headers);
        }

        /**
         * getToForSend
         *
         * @return string
         */
        public function getToForSend() {
            if (empty($this->to)) {
                return '';
            }
            return join(', ', $this->to);
        }

        /**
         * getUniqueId
         *
         * @return string
         */
        public function getUniqueId() {
            return md5(uniqid(time()));
        }

        /**
         * getWrapMessage
         *
         * @return string
         */
        public function getWrapMessage() {
            return wordwrap($this->message, $this->wrap);
        }
    }
