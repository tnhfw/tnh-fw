## TNH FRAMEWORK
**A simple PHP framework using HMVC architecture**

[![Latest Stable Version](https://poser.pugx.org/tnhfw/tnh-fw/v/stable)](https://packagist.org/packages/tnhfw/tnh-fw)
[![Latest Unstable Version](https://poser.pugx.org/tnhfw/tnh-fw/v/unstable)](https://packagist.org/packages/tnhfw/tnh-fw)
[![License](https://poser.pugx.org/tnhfw/tnh-fw/license)](https://packagist.org/packages/tnhfw/tnh-fw)
[![Build Status](https://img.shields.io/travis/tnhfw/tnh-fw/1.0.0-dev.svg?style=flat-square)](https://travis-ci.com/tnhfw/tnh-fw)
[![Quality Score](https://img.shields.io/scrutinizer/g/tnhfw/tnh-fw.svg?style=flat-square)](https://scrutinizer-ci.com/g/tnhfw/tnh-fw)
[![Total Downloads](https://poser.pugx.org/tnhfw/tnh-fw/downloads)](https://packagist.org/packages/tnhfw/tnh-fw)
[![Maintainability](https://api.codeclimate.com/v1/badges/7cd6345e1f49603a0817/maintainability)](https://codeclimate.com/github/tnhfw/tnh-fw/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/7cd6345e1f49603a0817/test_coverage)](https://codeclimate.com/github/tnhfw/tnh-fw/test_coverage)

## Requirements

 - Any web server **(Apache, nginx, lighttp, etc.)**
 - **PHP >= 5.4**, **PHP 7**

## Installation 
### Using composer (recommended)
```bash
composer create-project tnhfw/tnh-fw --stability dev
```
### Using GitHub releases
 - Download the framework using the following link [https://github.com/tnhfw/tnh-fw/releases](https://github.com/tnhfw/tnh-fw/releases)
 - Extract the downloaded file in your web directory (**www, htdocs, web, etc.)** for example **/www/tnh-fw** or with your application name **/www/my-apps**
### Tests
You can make tests by running the command below in the root of project.
This need install composer dependencies using Dev. 
```bash
vendor/bin/phpunit -c tests
```
Now launch your browser with your url like **http://localhost/tnh-fw** or **http://localhost/my-apps** change localhost with your real IP address or domain name if your are not in local.

And next Go ! read the doc.
