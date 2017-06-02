<?php
/**
 * TNH Framework
 *
 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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

		public static function a($link = '', $anchor = null, array $attributes = array()){
			if(!is_url($link)){
				$link = Url::site_url($link);
			}
			if(!$anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "'.$link.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			return $str;
		}
		
		
		public static function mailto($link = '', $anchor = null, array $attributes = array()){
			if(!$anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "mailto:'.$link.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			return $str;
		}

	}
