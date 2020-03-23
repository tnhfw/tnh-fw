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

    class Email extends BaseClass{
        /**
         * @var int $wrap
         */
        protected $wrap = 78;

        /**
         * @var string 
         */
        protected $from;

        /**
         * @var array $to
         */
        protected $to = array();

        /**
         * @var array $cc
         */
        protected $cc = array();

        /**
         * @var array $bcc
         */
        protected $bcc = array();

        /**
         * @var string 
         */
        protected $replyTo;

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
         * Send mail protocol, current supported values are:
         * "mail", "smtp"
         * @var string
         */
        protected $protocol = 'mail';

        /**
         * SMTP configuration
         * @var array
         */
        protected $smtpConfig = array();

        /**
         * SMTP connection socket
         * @var resource|boolean
         */
        protected $smtpSocket;

        /**
         * The last SMTP server response string
         * @var string
         */
        protected $smtpResponse;

        /**
         * The last sending mail error
         * @var string|null
         */
        protected $error = null;

        /**
         * The log for sending mail
         * @var array
         */
        protected $logs = array();
        

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
            $this->from = null;
            $this->to = array();
            $this->cc = array();
            $this->bcc = array();
            $this->replyTo = null;
            $this->headers = array();
            $this->subject = null;
            $this->message = null;
            $this->wrap = 78;
            $this->params = null;
            $this->attachments = array();
            $this->logs = array();
            $this->error = null;
            $this->uid = md5(uniqid(time())); 
            $this->smtpResponse = null; 
            $this->smtpConfig = array(
                'transport'          => 'plain', //plain,tls
                'hostname'           => 'localhost',
                'port'               => 25,
                'username'           => null,
                'password'           => null,
                'connection_timeout' => 30,
                'response_timeout'   => 10
            );         
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
            $this->from = $this->formatHeader($email, $name);          
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
         * @params array $emails the list of recipient. This is an 
         * associative array name => email
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
            $this->cc = $pairs;
            return $this->addMailHeaders('Cc', $pairs);
        }

        /**
         * Return the list of Cc
         *
         * @return array
         */
        public function getCc() {
            return $this->cc;
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
            $this->bcc = $pairs;
            return $this->addMailHeaders('Bcc', $pairs);
        }

        /**
         * Return the list of Bcc
         *
         * @return array
         */
        public function getBcc() {
            return $this->bcc;
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
            $this->replyTo = $this->formatHeader($email, $name);   
            return $this->addMailHeader('Reply-To', $email, $name);
        }

        /**
         * setHtml
         *
         * @return object
         */
        public function setHtml() {
            $this->addGenericHeader('Content-Type', 'text/html; charset="utf-8"');           
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
            $this->filterOther((string) $subject));
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
         * 
         * @return object
         */
        public function addAttachment($path, $filename = null) {
            if (!file_exists($path)) {
                show_error('The email attachment file [' . $path . '] does not exists.');
                return $this;
            }
            if (empty($filename)) {
                $filename = basename($path);
            }
            $filename = $this->encodeUtf8($this->filterOther((string) $filename));
            $data = $this->getAttachmentData($path);
            $this->attachments[] = array(
                                        'path' => $path,
                                        'file' => $filename,
                                        'data' => chunk_split(base64_encode($data))
                                    );            
            return $this;
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
            $this->headers[$header] = $address;            
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
            $this->headers[$name] = $value;
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
         * getWrapMessage
         *
         * @return string
         */
        public function getWrapMessage() {
            return wordwrap($this->message, $this->wrap);
        }
    
        /**
         * Return the send mail protocol
         * @return string
         */
        public function getProtocol() {
            return $this->protocol;
        }

        /**
         * Set the send mail protocol to "mail"
         *
         * @return object the current instance
         */
        public function useMail() {
            $this->protocol = 'mail';
            return $this;
        }

        /**
         * Set the send mail protocol to "smtp"
         * 
         * @return object the current instance
         */
        public function useSmtp() {
            $this->protocol = 'smtp';
            return $this;
        }

        /**
         * Return all the smtp configurations
         * @return array
         */
        public function getSmtpConfigs() {
            return $this->smtpConfig;
        }

        /**
         * Return the smtp configuration value for the given name
         *
         * @param string $name the configuration name to get value
         * @param mixed $default the default value to return if can not find configuration
         * 
         * @return mixed the configuration value if exist or $default will be returned
         * returned
         */
        public function getSmtpConfig($name, $default = null) {
            $value = $default;
            if (array_key_exists($name, $this->smtpConfig)) {
                $value = $this->smtpConfig[$name];
            }
            return $value;
        }

        /**
         * Set the smtp configuration.
         *
         * The possible values are:
         * array(
         *      'transport'          => 'plain', //plain,tls
         *      'hostname'           => 'localhost', //smtp server hostname
         *      'port'               => 25, //smtp server port
         *      'username'           => null, //smtp username if required
         *      'password'           => null, //smtp password if required
         *      'connection_timeout' => 30, //connection timeout for smtp server socket
         *      'response_timeout'   => 10 //response timeout for smtp server socket reply
         *   );    
         *
         * NOTE: the configuration will be merged with the existing one
         *
         * @param array $config the configuration
         *
         * @return object the current instance
         */
        public function setSmtpConfig(array $config = array()) {
            $this->smtpConfig = array_merge($this->smtpConfig, $config);
            return $this;
        }

        /**
         * send the email
         *
         * @return boolean
         */
        public function send() {
            if (empty($this->to)) {
                show_error('Unable to send mail, no destination address has been set.');
                return false;
            }
            if (empty($this->from)) {
                show_error('Unable to send mail, no sender address has been set.');
                return false;
            }
            if ($this->protocol == 'mail') {
                return $this->sendMail();
            } else if ($this->protocol == 'smtp') {
                return $this->sendSmtp();
            }   
            return false;
        }


        /**
         * Return the last error when sending mail
         * @return string|null
         */
        public function getError() {
            return $this->error;
        }

        /**
         * Return the sending mail logs content
         * @return array
         */
        public function getLogs() {
            return $this->logs;
        }

        /**
         * Get attachment data
         *
         * @param string $path The path to the attachment file.
         *
         * @return string|boolean
         */
        protected function getAttachmentData($path) {
            $filesize = filesize($path);
            $handle = fopen($path, 'r');
            $attachment = null;
            if (is_resource($handle)) {
                $attachment = fread($handle, $filesize);
                fclose($handle);
            }
            return $attachment;
        }

        /**
         * assembleAttachment
         *
         * @return object
         */
        protected function setAttachmentHeaders() {
            $this->headers['MIME-Version'] = '1.0';
            $this->headers['Content-Type'] = "multipart/mixed; boundary=\"{$this->uid}\"";
            return $this;
        }

        /**
         * assembleAttachmentBody
         *
         * @return string
         */
        protected function assembleAttachmentBody() {
            $body = array();
            $body[] = 'This is a multi-part message in MIME format.';
            $body[] = "--{$this->uid}";
            $body[] = 'Content-Type: text/html; charset="utf-8"';
            $body[] = 'Content-Transfer-Encoding: base64';
            $body[] = PHP_EOL;
            $body[] = chunk_split(base64_encode($this->message));
            $body[] = PHP_EOL;
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
        protected function getAttachmentMimeTemplate($attachment) {
            $file = $attachment['file'];
            $data = $attachment['data'];

            $head = array();
            $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
            $head[] = 'Content-Transfer-Encoding: base64';
            $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
            $head[] = "";
            $head[] = $data;
            $head[] = "";
            $head[] = "--{$this->uid}";

            return implode(PHP_EOL, $head);
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
        protected function formatHeader($email, $name = null) {
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
        protected function encodeUtf8($value) {
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
        protected function encodeUtf8Word($value) {
            return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
        }

        /**
         * encodeUtf8Words
         *
         * @param string $value The words to encode.
         *
         * @return string
         */
        protected function encodeUtf8Words($value) {
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
        protected function filterEmail($email) {
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
        protected function filterName($name) {
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
        protected function filterOther($data) {
            return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
        }

        /**
         * Get destinataire for send
         *
         * @return string
         */
        protected function getToForSend() {
            return join(', ', $this->to);
        }

        /**
         * getHeadersForSend
         *
         * @return string
         */
        protected function getHeadersForSend() {
            $headers = null;
            foreach ($this->headers as $key => $value) {
                $headers .= $key . ': ' . $value . PHP_EOL;
            }
            return $headers;
        }

         /**
         * Get the attachment message for send or the simple message
         * @return string
         */
        protected function getMessageWithAttachmentForSend() {
            $message = $this->getWrapMessage();
            if ($this->hasAttachments()) {
                $this->setAttachmentHeaders();
                $message  = $this->assembleAttachmentBody();
            }
            return $message;
        }

        /**
         * Send smtp command to server
         * @param  string $command the smtp command
         * 
         * @return object the current instance
         */
        protected function sendCommand($command) {
            if (is_resource($this->smtpSocket)) {
                fputs($this->smtpSocket, $command . PHP_EOL);
            }
            $this->smtpResponse = $this->getSmtpServerResponse();
            return $this;
        }

        /**
         * Send EHLO or HELO command to smtp server
         * @return boolean true if server response is OK otherwise will return false
         */
        protected function sendHelloCommand() {
            $responseCode = $this->sendCommand('EHLO ' . $this->getSmtpClientHostname())
                                 ->getSmtpResponseCode();
            if ($responseCode !== 250) {
                $this->error = $this->smtpResponse;
                return false;
            }
            return true;
        }

        /**
         * Get the smtp server response
         * @return mixed
         */
        protected function getSmtpServerResponse() {
            $response = '';
            if (is_resource($this->smtpSocket)) {
                stream_set_timeout($this->smtpSocket, $this->getSmtpConfig('response_timeout', 10));
                while (($line = fgets($this->smtpSocket)) !== false) {
                    $response .= trim($line) . "\n";
                    if (substr($line, 3, 1) == ' ') {
                        break;
                    }
                }
                $response = trim($response);
            }
            return $response;
        }

        /**
         * Return the last response code
         *
         * @param string|null $response the response string if is null 
         * will use the last smtp response
         * 
         * @return integer the 3 digit of response code
         */
        protected function getSmtpResponseCode($response = null) {
            if ($response === null) {
                $response = $this->smtpResponse;
            }
            return (int) substr($response, 0, 3);
        }

        /**
         * Establish connection to smtp server
         * @return boolean 
         */
        protected function smtpConnection() {
            $this->smtpSocket = fsockopen(
                                        $this->getSmtpConfig('hostname'),
                                        $this->getSmtpConfig('port', 25),
                                        $errorNumber,
                                        $errorMessage,
                                        $this->getSmtpConfig('connection_timeout', 30)
                                    );

            if (! is_resource($this->smtpSocket)) {
                $this->error = $errorNumber . ': ' . $errorMessage;
                return false;
            }
            $response = $this->getSmtpServerResponse();
            $code = $this->getSmtpResponseCode($response);
            if ($code !== 220) {
                $this->error = $response;
                return false;
            }
            $this->logs['CONNECTION'] = $response;
            $hello = $this->sendHelloCommand();
            $this->logs['HELLO'] = $this->smtpResponse; 
            if (!$hello) {
                return false;
            }

            //Check if can use TLS connection to server
            if (!$this->checkForSmtpConnectionTls()) {
                return false;
            }

            //Authentication of the client
            if (!$this->smtpAuthentication()) {
                return false;
            }
            return true;
        }

        /**
         * Check if server support TLS connection
         * @return boolean
         */
        protected function checkForSmtpConnectionTls() {
            if ($this->getSmtpConfig('transport', 'plain') == 'tls') {
                $tlsCode = $this->sendCommand('STARTTLS')->getSmtpResponseCode();
                $this->logs['STARTTLS'] = $this->smtpResponse;
                if ($tlsCode === 220) {
                    /**
                     * STREAM_CRYPTO_METHOD_TLS_CLIENT is quite the mess ...
                     *
                     * - On PHP <5.6 it doesn't even mean TLS, but SSL 2.0, and there's no option to use actual TLS
                     * - On PHP 5.6.0-5.6.6, >=7.2 it means negotiation with any of TLS 1.0, 1.1, 1.2
                     * - On PHP 5.6.7-7.1.* it means only TLS 1.0
                     *
                     * We want the negotiation, so we'll force it below ...
                     */
                    if (is_resource($this->smtpSocket)) {
                        $method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                        if(version_compare(PHP_VERSION, '5.6', '>=')) {
                            $method = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                        }
                        stream_socket_enable_crypto($this->smtpSocket, true, $method);
                    }
                    $hello = $this->sendHelloCommand();
                    $this->logs['HELLO_TLS'] = $this->smtpResponse; 
                    if (!$hello) {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Client authentication
         * @return boolean
         */
        protected function smtpAuthentication() {
            $authCode = $this->sendCommand('AUTH LOGIN')->getSmtpResponseCode();
            $this->logs['AUTH_LOGIN'] = $this->smtpResponse;
            if ($authCode === 334) {
                $this->sendCommand(base64_encode($this->getSmtpConfig('username')))->getSmtpResponseCode();
                $code = $this->sendCommand(base64_encode($this->getSmtpConfig('password')))->getSmtpResponseCode();
                $this->logs['CLIENT_AUTH'] = $this->smtpResponse;
                if ($code !== 235) {
                    $this->error = $this->smtpResponse;
                    return false;
                }
            }
            return true;
        }

        /**
         * Send mail using "mail" protocol
         * @return boolean
         */
        protected function sendMail() {
            $to = $this->getToForSend();
            $message = $this->getMessageWithAttachmentForSend();
            $headers = $this->getHeadersForSend(); 
            $this->logger->info('Sending new mail using mail protocol, the information are listed below: '
                                  . 'destination: ' . $to . ', headers: ' . $headers . ', message: ' . $message);
            $result = mail($to, $this->subject, $message, $headers, $this->params);
            if (!$result) {
                $this->error = 'Error when sending mail using mail protocol';
            }
            return $result;
        }

         /**
         * Send mail using "smtp" protocol
         * @return boolean
         */
        protected function sendSmtp() {
            if (!$this->smtpConnection()) {
                return false;
            }
            $to = $this->getToForSend();
            $this->setSmtpAdditionnalHeaders();
            $message = $this->getMessageWithAttachmentForSend();
            $headers = $this->getHeadersForSend();
            $this->logger->info('Sending new mail using SMTP protocol, the information are listed below: '
                                  . 'destination: ' . $to . ', headers: ' . $headers . ', message: ' . $message);
            $recipients = array_merge($this->to, $this->cc, $this->bcc);
            $commands = array(
                                'mail_from' => array('MAIL FROM: <' . $this->from . '>', 'MAIL_FROM', 250),
                                'recipients' => array($recipients, 'RECIPIENTS'),
                                'date_1' => array('DATA', 'DATA_1', 354),
                                'date_2' => array($headers . PHP_EOL . $message . PHP_EOL . '.', 'DATA_2', 250),
                            );
            foreach ($commands as $key => $value) {
                if ($key == 'recipients') {
                    foreach ($value[0] as $address) {
                        $code = $this->sendCommand('RCPT TO: <' . $address . '>')->getSmtpResponseCode();
                        $this->logs[$value[1]][] = $this->smtpResponse;
                        if ($code !== 250) {
                            $this->error = $this->smtpResponse;
                            return false;
                        }
                    }
                } else {
                        $code = $this->sendCommand($value[0])->getSmtpResponseCode();
                        $this->logs[$value[1]] = $this->smtpResponse;
                        if ($code !== $value[2]) {
                            $this->error = $this->smtpResponse;
                            return false;
                        }
                }
            }
            $this->sendCommand('QUIT');
            $this->logs['QUIT'] = $this->smtpResponse;
            return empty($this->error);
        }

        /**
         * Set the additionnal headers for SMTP protocol
         */
        protected function setSmtpAdditionnalHeaders() {
            $to = $this->getToForSend();
            $additionalHeaders = array(
                'Date' => date('r'),
                'Subject' => $this->subject,
                'Return-Path' => $this->from,
                'To' => $to
            );
            foreach ($additionalHeaders as $key => $value) {
                if (! isset($this->headers[$key])) {
                    $this->headers[$key] = $value;
                }
            }
        }


         /**
         * Return the client hostname for SMTP
         * 
         * There are only two legal types of hostname - either a fully
         * qualified domain name (eg: "mail.example.com") or an IP literal
         * (eg: "[1.2.3.4]").
         *
         * @link    https://tools.ietf.org/html/rfc5321#section-2.3.5
         * @link    http://cbl.abuseat.org/namingproblems.html
         * @return string
         */
        protected function getSmtpClientHostname() {
            $globals = &class_loader('GlobalVar', 'classes');
            if ($globals->server('SERVER_NAME')) {
                return $globals->server('SERVER_NAME');
            }
            if ($globals->server('SERVER_ADDR')) {
                return $globals->server('SERVER_ADDR');
            }
            return '[127.0.0.1]';
        }

        /**
         * Class desctructor
         * @codeCoverageIgnore
         */
        public function __destruct() {
            if (is_resource($this->smtpSocket)) {
                fclose($this->smtpSocket);
            }
        }
    }
