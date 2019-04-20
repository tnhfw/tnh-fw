<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
	 *
	 * This content is released under the GNU GPL License (GPL)
	 *
	 * Copyright (C) 2017 Tony NGUEREZA
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation; either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
	*/

	class Html{

		/**
		 * Generate the html anchor link
		 * @param  string $link       the href attribute value
		 * @param  string $anchor     the displayed anchor
		 * @param  array  $attributes the additional attributes to be added
		 * @param boolean $return whether need return the generated html or just display it directly
		 *
		 * @return string|void             the anchor link generated html if $return is true or display it if not
		 */
		public static function a($link = '', $anchor = null, array $attributes = array(), $return = true){
			if(! is_url($link)){
				$link = Url::site_url($link);
			}
			if(! $anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "'.$link.'"';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			if($return){
				return $str;
			}
			echo $str;
		}
		
		/**
		 * Generate an mailto anchor link
		 * @param  string $link       the email address 
		 * @param  string $anchor     the displayed value of the link
		 * @param  array  $attributes the additional attributes to be added
		 * @param boolean $return whether need return the generated html or just display it directly
		 *
		 * @return string|void             the generated html for mailto link if $return is true or display it if not
		 */
		public static function mailto($link = '', $anchor = null, array $attributes = array(), $return = true){
			if(! $anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "mailto:'.$link.'"';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html "br" tag  
		 * @param  integer $nb the number of generated "<br />" tag
		 * @param boolean $return whether need return the generated html or just display it directly
		 *
		 * @return string|void      the generated "br" html if $return is true or display it if not
		 */
		public static function br($nb = 1, $return = true){
			if(! is_numeric($nb) || $nb <= 0){
				$nb = 1;
			}
			$str = null;
			for ($i = 1; $i <= $nb; $i++) {
				$str .= '<br />';
			}

			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html content for tag "hr"
		 * @param integer $nb the number of generated "<hr />" tag
		 * @param  array   $attributes the tag attributes
		 * @param  boolean $return    whether need return the generated html or just display it directly
		 *
		 * @return string|void the generated "hr" html if $return is true or display it if not.
		 */
		public static function hr($nb = 1, array $attributes = array(), $return = true){
			if(! is_numeric($nb) || $nb <= 0){
				$nb = 1;
			}
			$str = null;
			for ($i = 1; $i <= $nb; $i++) {
				$str .= '<hr' .attributes_to_string($attributes). '/>';
			}
			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html content for tag like h1, h2, h3, h4, h5 and h6
		 * @param  integer $type       the tag type 1 mean h1, 2 h2, etc,
		 * @param  string  $text       the display text
		 * @param integer $nb the number of generated "<h{1,2,3,4,5,6}>"
		 * @param  array   $attributes the tag attributes
		 * @param  boolean $return    whether need return the generated html or just display it directly
		 *
		 * @return string|void the generated header html if $return is true or display it if not.
		 */
		public static function head($type = 1, $text = null, $nb = 1, array $attributes = array(), $return = true){
			if(! is_numeric($nb) || $nb <= 0){
				$nb = 1;
			}
			if(! is_numeric($type) || $type <= 0 || $type > 6){
				$type = 1;
			}
			$str = null;
			for ($i = 1; $i <= $nb; $i++) {
				$str .= '<h' . $type . attributes_to_string($attributes). '>' .$text. '</h' . $type . '>';
			}
			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html "ul" tag
		 * @param  array   $data the data to use for each "li" tag
		 * @param  array   $attributes   the "ul" properties attribute use the array index below for each tag:
		 *  for ul "ul", for li "li".
		 * @param  boolean $return whether need return the generated html or just display it directly
		 *
		 * @return string|void the generated "ul" html  if $return is true or display it if not.
		 */
		public static function ul($data = array(), $attributes = array(), $return = true){
			$data = (array) $data;
			$str = null;
			$str .= '<ul' . (! empty($attributes['ul']) ? attributes_to_string($attributes['ul']):'') . '>';
			foreach ($data as $row) {
				$str .= '<li' . (! empty($attributes['li']) ? attributes_to_string($attributes['li']):'') .'>' .$row. '</li>';
			}
			$str .= '</ul>';
			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html "ol" tag
		 * @param  array   $data the data to use for each "li" tag
		 * @param  array   $attributes   the "ol" properties attribute use the array index below for each tag:
		 *  for ol "ol", for li "li".
		 * @param  boolean $return whether need return the generated html or just display it directly
		 * @return string|void the generated "ol" html  if $return is true or display it if not.
		 */
		public static function ol($data = array(), $attributes = array(), $return = true){
			$data = (array) $data;
			$str = null;
			$str .= '<ol' . (!empty($attributes['ol']) ? attributes_to_string($attributes['ol']):'') . '>';
			foreach ($data as $row) {
				$str .= '<li' . (!empty($attributes['li']) ? attributes_to_string($attributes['li']):'') .'>' .$row. '</li>';
			}
			$str .= '</ol>';
			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * Generate the html "table" tag
		 * @param  array   $headers            the table headers to use between (<thead>)
		 * @param  array   $body the table body values between (<tbody>)
		 * @param  array   $attributes   the table properties attribute use the array index below for each tag:
		 *  for table "table", for thead "thead", for thead tr "thead_tr",
		 *  for thead th "thead_th", for tbody "tbody", for tbody tr "tbody_tr", for tbody td "tbody_td", for tfoot "tfoot",
		 *  for tfoot tr "tfoot_tr", for tfoot th "tfoot_th".
		 * @param boolean $use_footer whether need to generate table footer (<tfoot>) use the $headers values
		 * @param  boolean $return whether need return the generated html or just display it directly
		 * @return string|void the generated "table" html  if $return is true or display it if not.
		 */
		public static function table($headers = array(), $body = array(), $attributes = array(), $use_footer = false, $return = true){
			$headers = (array) $headers;
			$body = (array) $body;
			$str = null;
			$str .= '<table' . (! empty($attributes['table']) ? attributes_to_string($attributes['table']):'') . '>';
			if(! empty($headers)){
				$str .= '<thead' . (! empty($attributes['thead']) ? attributes_to_string($attributes['thead']):'') .'>';
				$str .= '<tr' . (! empty($attributes['thead_tr']) ? attributes_to_string($attributes['thead_tr']):'') .'>';
				foreach ($headers as $value) {
					$str .= '<th' . (! empty($attributes['thead_th']) ? attributes_to_string($attributes['thead_th']):'') .'>' .$value. '</th>';
				}
				$str .= '</tr>';
				$str .= '</thead>';
			}
			else{
				//no need check for footer
				$use_footer = false;
			}
			$str .= '<tbody' . (! empty($attributes['tbody']) ? attributes_to_string($attributes['tbody']):'') .'>';
			foreach ($body as $row) {
				if(is_array($row)){
					$str .= '<tr' . (! empty($attributes['tbody_tr']) ? attributes_to_string($attributes['tbody_tr']):'') .'>';
					foreach ($row as $value) {
						$str .= '<td' . (! empty($attributes['tbody_td']) ? attributes_to_string($attributes['tbody_td']):'') .'>' .$value. '</td>';	
					}
					$str .= '</tr>';
				}
			}
			$str .= '</tbody>';
			if($use_footer){
				$str .= '<tfoot' . (! empty($attributes['tfoot']) ? attributes_to_string($attributes['tfoot']):'') .'>';
				$str .= '<tr' . (! empty($attributes['tfoot_tr']) ? attributes_to_string($attributes['tfoot_tr']):'') .'>';
				foreach ($headers as $value) {
					$str .= '<th' . (! empty($attributes['tfoot_th']) ? attributes_to_string($attributes['tfoot_th']):'') .'>' .$value. '</th>';
				}
				$str .= '</tr>';
				$str .= '</tfoot>';
			}
			$str .= '</table>';
			if($return){
				return $str;
			}
			echo $str;
		}
	}
