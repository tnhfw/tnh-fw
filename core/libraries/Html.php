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
			$link = Url::site_url($link);
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
		public static function mailto($link, $anchor = null, array $attributes = array(), $return = true){
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
			$nb = (int) $nb;
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
			$nb = (int) $nb;
			$str = null;
			for ($i = 1; $i <= $nb; $i++) {
				$str .= '<hr' .attributes_to_string($attributes). ' />';
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
			$nb = (int) $nb;
			$type = (int) $type;
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
			if($return){
				return self::buildUlOl($data, $attributes, true, 'ul');
			}
			self::buildUlOl($data, $attributes, false, 'ul');
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
			if($return){
				return self::buildUlOl($data, $attributes, true, 'ol');
			}
			self::buildUlOl($data, $attributes, false, 'ol');
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
			$tableAttributes = '';
			if(! empty($attributes['table'])){
				$tableAttributes = ' ' . attributes_to_string($attributes['table']);
			}
			$str .= '<table' . $tableAttributes . '>';
			$str .= self::buildTableHeader($headers, $attributes);
			$str .= self::buildTableBody($body, $attributes);

			if($use_footer){
				$str .= self::buildTableFooter($headers, $attributes);
			}
			$str .= '</table>';
			if($return){
				return $str;
			}
			echo $str;
		}

		/**
		 * This method is used to build the header of the html table
		 * @see  Html::table 
		 * @return string|null
		 */
		protected static function buildTableHeader(array $headers, $attributes = array()){
			$str = null;
			$theadAttributes = '';
			if(! empty($attributes['thead'])){
				$theadAttributes = ' ' . attributes_to_string($attributes['thead']);
			}
			$theadtrAttributes = '';
			if(! empty($attributes['thead_tr'])){
				$theadtrAttributes = ' ' . attributes_to_string($attributes['thead_tr']);
			}
			$thAttributes = '';
			if(! empty($attributes['thead_th'])){
				$thAttributes = ' ' . attributes_to_string($attributes['thead_th']);
			}
			$str .= '<thead' . $theadAttributes .'>';
			$str .= '<tr' . $theadtrAttributes .'>';
			foreach ($headers as $value) {
				$str .= '<th' . $thAttributes .'>' .$value. '</th>';
			}
			$str .= '</tr>';
			$str .= '</thead>';
			return $str;
		}

		/**
		 * This method is used to build the body of the html table
		 * @see  Html::table 
		 * @return string|null
		 */
		protected static function buildTableBody(array $body, $attributes = array()){
			$str = null;
			$tbodyAttributes = '';
			if(! empty($attributes['tbody'])){
				$tbodyAttributes = ' ' . attributes_to_string($attributes['tbody']);
			}
			$tbodytrAttributes = '';
			if(! empty($attributes['tbody_tr'])){
				$tbodytrAttributes = ' ' . attributes_to_string($attributes['tbody_tr']);
			}
			$tbodytdAttributes = '';
			if(! empty($attributes['tbody_td'])){
				$tbodytdAttributes = ' ' . attributes_to_string($attributes['tbody_td']);
			}
			$str .= '<tbody' . $tbodyAttributes .'>';
			$str .= self::buildTableBodyContent($body, $tbodytrAttributes, $tbodytdAttributes);
			$str .= '</tbody>';
			return $str;
		}

		/**
		 * This method is used to build the body content of the html table
		 * @param  array  $body              the table body data
		 * @param  string $tbodytrAttributes the html attributes for each tr in tbody
		 * @param  string $tbodytdAttributes the html attributes for each td in tbody
		 * @return string                    
		 */
		protected static function buildTableBodyContent(array $body, $tbodytrAttributes, $tbodytdAttributes){
			$str = null;
			foreach ($body as $row) {
				if(is_array($row)){
					$str .= '<tr' . $tbodytrAttributes .'>';
					foreach ($row as $value) {
						$str .= '<td' . $tbodytdAttributes .'>' .$value. '</td>';	
					}
					$str .= '</tr>';
				}
			}
			return $str;
		}

		/**
		 * This method is used to build the footer of the html table
		 * @see  Html::table 
		 * @return string|null
		 */
		protected static function buildTableFooter(array $footers, $attributes = array()){
			$str = null;
			$tfootAttributes = '';
			if(! empty($attributes['tfoot'])){
				$tfootAttributes = ' ' . attributes_to_string($attributes['tfoot']);
			}
			$tfoottrAttributes = '';
			if(! empty($attributes['tfoot_tr'])){
				$tfoottrAttributes = ' ' . attributes_to_string($attributes['tfoot_tr']);
			}
			$thAttributes = '';
			if(! empty($attributes['tfoot_th'])){
				$thAttributes = ' ' . attributes_to_string($attributes['tfoot_th']);
			}
			$str .= '<tfoot' . $tfootAttributes .'>';
				$str .= '<tr' . $tfoottrAttributes .'>';
				foreach ($footers as $value) {
					$str .= '<th' . $thAttributes .'>' .$value. '</th>';
				}
				$str .= '</tr>';
				$str .= '</tfoot>';
			return $str;
		}

		/**
		 * Return the HTML content for ol or ul tags
		 * @see  Html::ol
		 * @see  Html::ul
		 * @param  string  $olul   the type 'ol' or 'ul'
		 * @return void|string
		 */
		protected static function buildUlOl($data = array(), $attributes = array(), $return = true, $olul = 'ul'){
			$data = (array) $data;
			$str = null;
			$olulAttributes = '';
			if(! empty($attributes[$olul])){
				$olulAttributes = ' ' . attributes_to_string($attributes[$olul]);
			}
			$liAttributes = '';
			if(! empty($attributes['li'])){
				$liAttributes = ' ' . attributes_to_string($attributes['li']);
			}
			$str .= '<' . $olul . $olulAttributes . '>';
			foreach ($data as $row) {
				$str .= '<li' . $liAttributes .'>' .$row. '</li>';
			}
			$str .= '</' . $olul . '>';
			if($return){
				return $str;
			}
			echo $str;
		}
	}
